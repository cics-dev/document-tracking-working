<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DTS-ZPPSU | Document Tracking System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    :root {
      --primary: #800000;
      --secondary: #ffc107;
      --light-gray: #f5f5f5;
      --dark-gray: #757575;
      --success: #4caf50;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background-color: #fafafa;
      color: #333;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .tracking-container {
      background-color: white;
      border-radius: 8px;
      padding: 25px;
      margin-top: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .tracking-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }

    .order-id {
      font-size: 18px;
      font-weight: 600;
    }

    .order-id span {
      color: var(--primary);
    }

    .tracking-progress {
      display: flex;
      justify-content: space-between;
      position: relative;
      margin: 40px 0;
    }

    .progress-line {
      position: absolute;
      height: 3px;
      background-color: #e0e0e0;
      top: 15px;
      left: 0;
      right: 0;
      z-index: 1;
    }

    .progress-line-active {
      background-color: var(--primary);
      width: 75%;
      transition: width 0.5s ease;
    }

    .status-step {
      display: flex;
      flex-direction: column;
      align-items: center;
      z-index: 2;
    }

    .status-icon {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background-color: #e0e0e0;
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 10px;
      color: white;
    }

    .status-icon.active {
      background-color: var(--primary);
    }

    .status-icon.completed {
      background-color: var(--success);
    }

    .status-label {
      font-size: 14px;
      text-align: center;
      color: var(--dark-gray);
    }

    .status-label.active {
      color: var(--primary);
      font-weight: 600;
    }

    .status-date {
      font-size: 12px;
      color: var(--dark-gray);
      margin-top: 5px;
    }

    .courier-info {
      background-color: var(--light-gray);
      padding: 15px;
      border-radius: 8px;
      margin: 30px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .courier-name {
      font-weight: 600;
    }

    .tracking-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .status-updates h3 {
      margin-top: 20px;
    }

    .update-card {
      display: flex;
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }

    .update-icon {
      margin-right: 15px;
      color: var(--primary);
    }

    .update-time {
      color: var(--dark-gray);
      font-size: 13px;
    }

    .action-buttons {
      text-align: right;
      margin-top: 20px;
    }

    .btn {
      padding: 10px 20px;
      border-radius: 4px;
      font-weight: 500;
      cursor: pointer;
      border: none;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
    }

    .btn-outline {
      background-color: transparent;
      border: 1px solid var(--primary);
      color: var(--primary);
    }
  </style>
</head>
<body>
  <div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Document Tracking</h1>
    <div class="tracking-container">
      <div class="tracking-header">
        <div class="order-id">Document Reference: <span>{{ $document->document_number }}</span></div>
      </div>

      <div class="tracking-progress">
        <div class="progress-line">
          <div class="progress-line-active"></div>
        </div>

        <div class="status-step">
          <div class="status-icon completed"><i class="fas fa-edit"></i></div>
          <div class="status-label">Filed</div>
          <div class="status-date">Jul 12, 05:19 AM</div>
        </div>

        <div class="status-step">
          <div class="status-icon active"><i class="fas fa-share"></i></div>
          <div class="status-label active">Sent</div>
          <div class="status-date">Jul 12, 05:19 AM</div>
        </div>

        <div class="status-step">
          <div class="status-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="status-label">Processing</div>
          <div class="status-date">-</div>
        </div>

        <div class="status-step">
          <div class="status-icon"><i class="fas fa-check-circle"></i></div>
          <div class="status-label">Completed</div>
          <div class="status-date">-</div>
        </div>
      </div>

      <div class="courier-info">
        <div>
          <div class="courier-name">Subject: {{ $document->subject }}</div>
          <div class="tracking-number">Status: {{ $document->status }}</div>
        </div>
        <a href="#" class="tracking-link">View History <i class="fas fa-external-link-alt"></i></a>
      </div>

      <div class="status-updates">
        <h3>Document Activity Logs</h3>
        @foreach($document->logs->sortByDesc('created_at') as $log)
        <div class="update-card">
          <div class="update-icon">
            @if($log->action == 'Document Sent')
              <i class="fas fa-share-square"></i>
            @else
              <i class="fas fa-info-circle"></i>
            @endif
          </div>
          <div>
            <div>
              @if($log->action == 'Document Sent')
                Document Sent
              @elseif(strpos($log->action, 'viewed') !== false)
                {{ $log->description }}
              @else
                {{ $log->description }}
              @endif
            </div>
            <div class="update-time">{{ $log->created_at->format('F d, Y h:i A') }}</div>
          </div>
        </div>
        @endforeach
      </div>

      <div class="action-buttons">
        <button class="btn btn-primary">Contact Office</button>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const progressLine = document.querySelector('.progress-line-active');
      const currentStatus = '{{ strtolower($document->status) }}';

      switch(currentStatus) {
        case 'filed': progressLine.style.width = '0%'; break;
        case 'sent': progressLine.style.width = '33%'; break;
        case 'processing': progressLine.style.width = '66%'; break;
        case 'completed': progressLine.style.width = '100%'; break;
        default: progressLine.style.width = '0%';
      }

      document.querySelector('.tracking-link').addEventListener('click', function(e) {
        e.preventDefault();
        alert('Opening full document history...');
      });
    });
  </script>
</body>
</html>