import socket
import threading
from queue import Queue
import time

print_lock = threading.Lock()

def portscan(port, target):
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    try:
        con = s.connect((target, port))
        with print_lock:
            print(f'Port {port} is open')
        con.close()
    except:
        pass

def threader(q, target):
    while True:
        worker = q.get()
        portscan(worker, target)
        q.task_done()

def main():
    target = '127.0.0.1'  # localhost
    print(f'[*] Starting port scan on {target}')
    print('[*] This might take a few minutes...')
    
    # Create queue and threader
    q = Queue()
    
    # Start threads
    for x in range(100):
        t = threading.Thread(target=threader, args=(q, target))
        t.daemon = True
        t.start()
    
    # Common ports to scan
    ports = [
        20, 21, 22, 23, 25, 53, 80, 110, 111, 135, 139, 143, 443, 445, 993, 995, 1723, 3306, 3389, 5900, 8080
    ]
    
    # Add ports to queue
    for port in ports:
        q.put(port)
    
    # Wait for all tasks to complete
    q.join()
    print('[*] Scan completed!')

if __name__ == '__main__':
    main() 