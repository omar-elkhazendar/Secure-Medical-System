<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['user'];

// Get doctor id
$stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
$stmt->execute([$user['id']]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

// Handle new post
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['content']) &&
    !isset($_POST['comment_post_id'])
) {
    $content = trim($_POST['content']);
    if ($content !== '') {
        $stmt = $pdo->prepare('INSERT INTO posts (doctor_id, content) VALUES (?, ?)');
        $stmt->execute([$doctor_id, $content]);
        $post_id = $pdo->lastInsertId();
        // Handle attachments
        if (!empty($_FILES['attachments']['name'][0])) {
            $allowed_ext = ['jpg','jpeg','png','gif','bmp','pdf','doc','docx','xls','xlsx','txt','zip','rar'];
            $upload_dir = '../uploads/posts/';
            foreach ($_FILES['attachments']['name'] as $i => $name) {
                $tmp_name = $_FILES['attachments']['tmp_name'][$i];
                $error = $_FILES['attachments']['error'][$i];
                $size = $_FILES['attachments']['size'][$i];
                if ($error === UPLOAD_ERR_OK && $size <= 10*1024*1024) { // 10MB max
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed_ext)) {
                        $new_name = uniqid('post_', true) . '.' . $ext;
                        $dest = $upload_dir . $new_name;
                        if (move_uploaded_file($tmp_name, $dest)) {
                            $file_type = in_array($ext, ['jpg','jpeg','png','gif','bmp']) ? 'image' : 'file';
                            $stmt = $pdo->prepare('INSERT INTO post_attachments (post_id, file_path, file_type) VALUES (?, ?, ?)');
                            $stmt->execute([$post_id, 'uploads/posts/' . $new_name, $file_type]);
                        }
                    }
                }
            }
        }
        header('Location: social_feed.php');
        exit;
    }
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_post_id']) && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    $comment_post_id = intval($_POST['comment_post_id']);
    if ($comment !== '') {
        $stmt = $pdo->prepare('INSERT INTO comments (post_id, doctor_id, comment) VALUES (?, ?, ?)');
        $stmt->execute([$comment_post_id, $doctor_id, $comment]);
        header('Location: social_feed.php');
        exit;
    }
}

// Handle like/unlike
if (isset($_GET['like']) && isset($_GET['post_id'])) {
    $post_id = intval($_GET['post_id']);
    // Check if already liked
    $stmt = $pdo->prepare('SELECT id FROM post_likes WHERE post_id = ? AND doctor_id = ?');
    $stmt->execute([$post_id, $doctor_id]);
    $like = $stmt->fetch();
    if ($like) {
        // Unlike
        $stmt = $pdo->prepare('DELETE FROM post_likes WHERE id = ?');
        $stmt->execute([$like['id']]);
    } else {
        // Like
        $stmt = $pdo->prepare('INSERT INTO post_likes (post_id, doctor_id) VALUES (?, ?)');
        $stmt->execute([$post_id, $doctor_id]);
    }
    header('Location: social_feed.php');
    exit;
}

// Handle delete post
if (isset($_GET['delete_post'])) {
    $delete_post_id = intval($_GET['delete_post']);
    // Ensure the post belongs to the current doctor
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND doctor_id = ?');
    $stmt->execute([$delete_post_id, $doctor_id]);
    if ($stmt->fetch()) {
        // Delete post (comments and likes will be deleted by ON DELETE CASCADE)
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$delete_post_id]);
    }
    header('Location: social_feed.php');
    exit;
}

// Handle delete comment
if (isset($_GET['delete_comment'])) {
    $delete_comment_id = intval($_GET['delete_comment']);
    // Ensure the comment belongs to the current doctor
    $stmt = $pdo->prepare('SELECT * FROM comments WHERE id = ? AND doctor_id = ?');
    $stmt->execute([$delete_comment_id, $doctor_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
        $stmt->execute([$delete_comment_id]);
    }
    header('Location: social_feed.php');
    exit;
}

// Fetch all posts (latest first) with likes and comments
$stmt = $pdo->prepare('
    SELECT p.*, d.specialization, u.username
    FROM posts p
    JOIN doctors d ON p.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    ORDER BY p.created_at DESC
');
$stmt->execute();
$posts = $stmt->fetchAll();

// Fetch likes for all posts
$post_ids = array_column($posts, 'id');
$likes = [];
$user_likes = [];
if ($post_ids) {
    $in = str_repeat('?,', count($post_ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT post_id, COUNT(*) as like_count FROM post_likes WHERE post_id IN ($in) GROUP BY post_id");
    $stmt->execute($post_ids);
    foreach ($stmt->fetchAll() as $row) {
        $likes[$row['post_id']] = $row['like_count'];
    }
    // Get user likes
    $stmt = $pdo->prepare("SELECT post_id FROM post_likes WHERE post_id IN ($in) AND doctor_id = ?");
    $stmt->execute(array_merge($post_ids, [$doctor_id]));
    foreach ($stmt->fetchAll() as $row) {
        $user_likes[$row['post_id']] = true;
    }
}
// Fetch comments for all posts
$comments = [];
if ($post_ids) {
    $in = str_repeat('?,', count($post_ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT c.*, u.username, d.specialization FROM comments c JOIN doctors d ON c.doctor_id = d.id JOIN users u ON d.user_id = u.id WHERE c.post_id IN ($in) ORDER BY c.created_at ASC");
    $stmt->execute($post_ids);
    foreach ($stmt->fetchAll() as $row) {
        $comments[$row['post_id']][] = $row;
    }
}

// جلب عدد المتابعين لكل طبيب
$doctor_ids = array_unique(array_column($posts, 'doctor_id'));
$followers_counts = [];
$following_doctors = [];
if ($doctor_ids) {
    $in = str_repeat('?,', count($doctor_ids) - 1) . '?';
    // عدد المتابعين لكل طبيب
    $stmt = $pdo->prepare("SELECT doctor_id, COUNT(*) as cnt FROM doctor_followers WHERE doctor_id IN ($in) GROUP BY doctor_id");
    $stmt->execute($doctor_ids);
    foreach ($stmt->fetchAll() as $row) {
        $followers_counts[$row['doctor_id']] = $row['cnt'];
    }
    // من أتابعهم أنا
    $stmt = $pdo->prepare("SELECT doctor_id FROM doctor_followers WHERE follower_id = ? AND doctor_id IN ($in)");
    $stmt->execute(array_merge([$doctor_id], $doctor_ids));
    foreach ($stmt->fetchAll() as $row) {
        $following_doctors[$row['doctor_id']] = true;
    }
}

// جلب مرفقات كل منشور
$post_attachments = [];
if ($post_ids) {
    $in = str_repeat('?,', count($post_ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM post_attachments WHERE post_id IN ($in)");
    $stmt->execute($post_ids);
    foreach ($stmt->fetchAll() as $row) {
        $post_attachments[$row['post_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors Social Feed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f4f6fa; font-family: 'Poppins', sans-serif; }
        .feed-container { max-width: 700px; margin: 40px auto; }
        .post-form textarea { resize: none; }
        .post-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 8px #0001; margin-bottom: 24px; padding: 20px; }
        .post-header { display: flex; align-items: center; margin-bottom: 10px; }
        .post-avatar { width: 48px; height: 48px; border-radius: 50%; background: #e3e3e3; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-right: 12px; }
        .post-meta { font-size: 0.95rem; color: #888; }
        .post-content { font-size: 1.1rem; margin-bottom: 10px; }
        .post-actions { display: flex; gap: 16px; }
        .post-actions form { display: inline; }
        .comment-section { margin-top: 10px; padding-left: 20px; }
        .comment { background: #f7f7f7; border-radius: 10px; padding: 8px 12px; margin-bottom: 6px; }
        .comment-meta { font-size: 0.85rem; color: #666; }
        .post-image-container {
            display: block;
            margin: 10px 0;
            text-align: center;
        }
        
        .post-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        .post-image:hover {
            transform: scale(1.02);
        }
        
        @media (min-width: 768px) {
            .post-image {
                max-width: 600px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare Doctor
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="followers.php"><i class="fas fa-users"></i> Followers</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="feed-container">
        <div class="post-form mb-4">
            <form id="add-post-form" method="POST" action="" enctype="multipart/form-data">
                <textarea name="content" class="form-control mb-2" rows="3" placeholder="Share something with your colleagues..." required></textarea>
                <input type="file" name="attachments[]" class="form-control mb-2" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
                <button type="submit" class="btn btn-info"><i class="fas fa-paper-plane me-1"></i> Post</button>
            </form>
        </div>
        <?php foreach ($posts as $post): ?>
            <div class="post-card" data-post-id="<?php echo $post['id']; ?>">
                <div class="post-header">
                    <div class="post-avatar"><i class="fas fa-user-md"></i></div>
                    <div>
                        <div>
                            <b><?php echo htmlspecialchars($post['username']); ?></b>
                            <span class="text-muted">(<?php echo htmlspecialchars($post['specialization']); ?>)</span>
                            <span class="badge bg-info ms-2 followers-badge" data-doctor-id="<?php echo $post['doctor_id']; ?>">
                                <i class="fas fa-users"></i> <span class="followers-count"><?php echo isset($followers_counts[$post['doctor_id']]) ? $followers_counts[$post['doctor_id']] : 0; ?></span> Followers
                            </span>
                            <?php if ($post['doctor_id'] != $doctor_id): ?>
                                <button class="btn btn-outline-primary btn-sm follow-btn ms-2" data-doctor-id="<?php echo $post['doctor_id']; ?>">
                                    <i class="fas fa-user-plus"></i> <span class="follow-text"><?php echo isset($following_doctors[$post['doctor_id']]) ? 'Unfollow' : 'Follow'; ?></span>
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="post-meta"><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></div>
                    </div>
                    <?php if ($post['doctor_id'] == $doctor_id): ?>
                        <div style="margin-left:auto; display:flex; gap:5px;">
                            <button class="btn btn-warning btn-sm edit-post-btn"><i class="fas fa-edit"></i> Edit</button>
                            <a href="?delete_post=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="post-content-view"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                <?php if (!empty($post_attachments[$post['id']])): ?>
                    <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                        <?php foreach ($post_attachments[$post['id']] as $att): ?>
                            <?php if ($att['file_type'] === 'image'): ?>
                                <a href="<?php echo htmlspecialchars($att['file_path']); ?>" target="_blank" class="post-image-container">
                                    <img src="<?php echo htmlspecialchars($att['file_path']); ?>" alt="image" class="post-image">
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php foreach ($post_attachments[$post['id']] as $att): ?>
                            <?php if ($att['file_type'] !== 'image'): ?>
                                <a href="/<?php echo htmlspecialchars($att['file_path']); ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-paperclip"></i> <?php echo basename($att['file_path']); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($post['doctor_id'] == $doctor_id): ?>
                <form class="edit-post-form" style="display:none;">
                    <textarea class="form-control mb-2" name="edit_content" rows="3"><?php echo htmlspecialchars($post['content']); ?></textarea>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save"></i> Save</button>
                    <button type="button" class="btn btn-secondary btn-sm cancel-edit-btn">Cancel</button>
                </form>
                <?php endif; ?>
                <div class="post-actions mb-2">
                    <button class="btn btn-sm <?php echo isset($user_likes[$post['id']]) ? 'btn-success' : 'btn-outline-success'; ?> like-btn">
                        <i class="fas fa-thumbs-up"></i> Like (<span class="like-count"><?php echo isset($likes[$post['id']]) ? $likes[$post['id']] : 0; ?></span>)
                    </button>
                </div>
                <div class="comment-section">
                    <div class="comments-list">
                    <?php if (!empty($comments[$post['id']])): ?>
                        <?php foreach ($comments[$post['id']] as $comment): ?>
                            <div class="comment d-flex justify-content-between align-items-center" data-comment-id="<?php echo $comment['id']; ?>">
                                <div>
                                    <div><b><?php echo htmlspecialchars($comment['username']); ?></b> <span class="text-muted">(<?php echo htmlspecialchars($comment['specialization']); ?>)</span></div>
                                    <div><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                                    <div class="comment-meta"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></div>
                                </div>
                                <?php if ($comment['doctor_id'] == $doctor_id): ?>
                                    <a href="?delete_comment=<?php echo $comment['id']; ?>" class="btn btn-outline-danger btn-sm ms-2" onclick="return confirm('Delete this comment?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                    <form class="add-comment-form mt-2">
                        <input type="hidden" name="comment_post_id" value="<?php echo $post['id']; ?>">
                        <textarea name="comment" class="form-control form-control-sm mb-1" rows="1" placeholder="Write a comment..." required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-comment-dots me-1"></i> Comment</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
            <div class="alert alert-info text-center">No posts yet. Be the first to share something!</div>
        <?php endif; ?>
    </div>
    <script>
    // Like button AJAX
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const postCard = btn.closest('.post-card');
            const postId = postCard.getAttribute('data-post-id');
            fetch('social_feed_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'like', post_id: postId})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.classList.toggle('btn-success', data.liked);
                    btn.classList.toggle('btn-outline-success', !data.liked);
                    btn.querySelector('.like-count').textContent = data.count;
                }
            });
        });
    });
    // Add comment AJAX
    document.querySelectorAll('.add-comment-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const postCard = form.closest('.post-card');
            const postId = postCard.getAttribute('data-post-id');
            const textarea = form.querySelector('textarea[name="comment"]');
            const comment = textarea.value.trim();
            if (!comment) return;
            fetch('social_feed_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'comment', post_id: postId, comment: comment})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const c = data.comment;
                    const commentDiv = document.createElement('div');
                    commentDiv.className = 'comment d-flex justify-content-between align-items-center';
                    commentDiv.innerHTML = `<div><div><b>${c.username}</b> <span class='text-muted'>(${c.specialization})</span></div><div>${c.comment.replace(/\n/g, '<br>')}</div><div class='comment-meta'>${c.created_at}</div></div>`;
                    if (c.doctor_id == <?php echo $doctor_id; ?>) {
                        commentDiv.innerHTML += `<a href="?delete_comment=${c.id}" class="btn btn-outline-danger btn-sm ms-2" onclick="return confirm('Delete this comment?');"><i class="fas fa-trash"></i></a>`;
                    }
                    postCard.querySelector('.comments-list').appendChild(commentDiv);
                    textarea.value = '';
                }
            });
        });
    });
    // Edit post (show form)
    document.querySelectorAll('.edit-post-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const postCard = btn.closest('.post-card');
            postCard.querySelector('.post-content-view').style.display = 'none';
            postCard.querySelector('.edit-post-form').style.display = 'block';
        });
    });
    // Cancel edit
    document.querySelectorAll('.cancel-edit-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const postCard = btn.closest('.post-card');
            postCard.querySelector('.edit-post-form').style.display = 'none';
            postCard.querySelector('.post-content-view').style.display = 'block';
        });
    });
    // Save edit AJAX
    document.querySelectorAll('.edit-post-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const postCard = form.closest('.post-card');
            const postId = postCard.getAttribute('data-post-id');
            const content = form.querySelector('textarea[name="edit_content"]').value.trim();
            fetch('social_feed_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'edit_post', post_id: postId, content: content})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    postCard.querySelector('.post-content-view').innerHTML = data.content.replace(/\n/g, '<br>');
                    form.style.display = 'none';
                    postCard.querySelector('.post-content-view').style.display = 'block';
                }
            });
        });
    });
    // حذف منشور ديناميكي
    document.querySelectorAll('.post-card .btn-danger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!btn.href.includes('delete_post')) return;
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this post?')) return;
            const postCard = btn.closest('.post-card');
            const postId = postCard.getAttribute('data-post-id');
            fetch('social_feed_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'delete_post', post_id: postId})
            })
            .then(res => res.json())
            .then(data => { if (data.success) postCard.remove(); });
        });
    });
    // حذف تعليق ديناميكي
    document.querySelectorAll('.comment .btn-outline-danger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!btn.href.includes('delete_comment')) return;
            e.preventDefault();
            if (!confirm('Delete this comment?')) return;
            const commentDiv = btn.closest('.comment');
            const commentId = commentDiv.getAttribute('data-comment-id');
            fetch('social_feed_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'delete_comment', comment_id: commentId})
            })
            .then(res => res.json())
            .then(data => { if (data.success) commentDiv.remove(); });
        });
    });
    // زر المتابعة/إلغاء المتابعة
    document.querySelectorAll('.follow-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const doctorId = btn.getAttribute('data-doctor-id');
            const action = btn.querySelector('.follow-text').textContent.trim() === 'Follow' ? 'follow' : 'unfollow';
            fetch('social_feed_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: action, target_doctor_id: doctorId})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.querySelector('.follow-text').textContent = (action === 'follow') ? 'Unfollow' : 'Follow';
                    // تحديث عدد المتابعين
                    const badge = document.querySelector('.followers-badge[data-doctor-id="'+doctorId+'"] .followers-count');
                    if (badge) badge.textContent = data.followers_count;
                }
            });
        });
    });
    </script>
</body>
</html> 