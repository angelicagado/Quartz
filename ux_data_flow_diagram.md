```mermaid
flowchart TD
    %% User Personas
    Admin((Admin/Organizer))
    Participant((Participant))
    System{Quartz System}

    %% Phase 1: Event Creation & Setup
    subgraph Pre-Event
        Admin -->|1. Creates Event| System
        System -->|2. Generates Invite Link & QR| Admin
        Admin -->|3. Shares Link/QR| Participant
    end

    %% Phase 2: Registration
    subgraph Registration
        Participant -->|4. Scans Invite QR / Clicks Link| System
        System -->|5. Validates Event (Public/Private)| System
        Participant -->|6. Fills Registration Form| System
        System -->|7. Issues Unique Participant QR| Participant
    end

    %% Phase 3: Event Day (Live Attendance)
    subgraph Event Day
        Participant -->|8. Presents Unique QR| Admin
        Admin -->|9. Scans QR (Camera/Upload)| System
        System -->|10. Logs Timestamp & Status (In/Out)| System
        System -->|11. Updates Live Dashboard Feed| Admin
    end

    %% Phase 4: Post-Event
    subgraph Post-Event
        System -->|12. Checks Evaluation Requirement| System
        Participant -->|13. Submits Evaluation Form| System
        System -->|14. Validates Attendance & Eval| System
        System -->|15. Generates Certificate PDF| System
        System -->|16. Issues Certificate| Participant
        Participant -->|17. Views/Downloads Certificate| Participant
    end
    
    %% Styling
    classDef user fill:#2563eb,stroke:#1d4ed8,stroke-width:2px,color:#fff;
    classDef system fill:#059669,stroke:#047857,stroke-width:2px,color:#fff;
    
    class Admin,Participant user;
    class System system;
```
