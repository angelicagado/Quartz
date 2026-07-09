```mermaid
flowchart TD
    %% User & Devices
    Admin[Admin / Superadmin\nDashboard & Event Config]
    Participant[Participant\nView Profile & Certificates]
    ScannerDevice[Organizer / Scanner Device\nCamera / Hardware Scanner]

    %% Front-End Layer
    subgraph Frontend [Front-End SPA (JavaScript/Tailwind)]
        WebApp[Web Application Interface]
        QRScanner[QR Code Scanner Module]
    end

    %% Back-End Layer
    subgraph Backend [Laravel Back-End (PHP)]
        Router[HTTP Routing & API]
        Auth[Authentication & Fortify]
        EventController[Event Management Logic]
        AttendanceController[Attendance Tracking System]
        CertEngine[Automated Certificate Engine]
    end

    %% Data Layer
    subgraph DataLayer [Data Layer]
        MySQL[(MySQL Database)]
        Redis[(Redis Cache / Queues)]
        S3[Cloud File Storage\nQR Codes, Templates]
    end

    %% Connections
    Admin <-->|Configures Events| WebApp
    Participant <-->|Views Data| WebApp
    ScannerDevice -->|Scans QR Code| QRScanner
    
    WebApp <-->|JSON Requests| Router
    QRScanner -->|POST /attendance| Router
    
    Router --> Auth
    Auth --> EventController
    Auth --> AttendanceController
    
    EventController <--> MySQL
    AttendanceController <--> MySQL
    
    AttendanceController -->|Dispatches Job| Redis
    Redis -->|Processes async| CertEngine
    CertEngine -->|Retrieves Template| S3
    CertEngine -->|Saves PDF| S3
    CertEngine -->|Updates Record| MySQL
```
