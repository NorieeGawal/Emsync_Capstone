# EMSYNC Draw.io Flowchart

Paste this Mermaid code into Draw.io using the Mermaid import option.

```mermaid
flowchart TD
    Start([Start]) --> Login[User Opens EMSYNC Login]
    Login --> EnterCredentials[Enter Email and Password]
    EnterCredentials --> Authenticate{Credentials Valid?}

    Authenticate -->|No| LoginError[Display Login Error]
    LoginError --> EnterCredentials

    Authenticate -->|Yes| DetermineRole{Check User Role}

    DetermineRole -->|Employee| EmployeeDashboard[Open Employee Dashboard]
    DetermineRole -->|Manager| ManagerDashboard[Open Manager Dashboard]
    DetermineRole -->|HR| HRDashboard[Open HR Dashboard]

    EmployeeDashboard --> ViewEmployeeData[View Personal Scores, Attendance and Feedback]
    ViewEmployeeData --> EndEmployee([End])

    ManagerDashboard --> ViewTeam[View Employees in Assigned Department]
    ViewTeam --> SelectEmployee[Select an Employee]
    SelectEmployee --> ViewTeamPerformance[View Employee KPI and Attendance]
    ViewTeamPerformance --> AddFeedback{Add Performance Feedback?}
    AddFeedback -->|Yes| SaveFeedback[Save Feedback to Database]
    SaveFeedback --> ViewTeamPerformance
    AddFeedback -->|No| EndManager([End])

    HRDashboard --> HRAction{Choose HR Action}

    HRAction -->|Manage Accounts| ManageAccounts[Open Employees Page]
    ManageAccounts --> AccountAction{Choose Account Action}
    AccountAction -->|Create Account| EnterAccount[Enter Name, Email, Password, Role and Department]
    EnterAccount --> ValidateAccount{Account Data Valid and Email Unique?}
    ValidateAccount -->|No| EnterAccount
    ValidateAccount -->|Yes| HashPassword[Hash Password]
    HashPassword --> SaveAccount[(Save User in users Table)]
    SaveAccount --> HRAction

    AccountAction -->|Edit or Archive Account| UpdateAccount[Update Account or Move to Archived Accounts]
    UpdateAccount --> SaveAccount

    HRAction -->|Manage Attendance| AttendancePage[Open Attendance Bulk Page]
    AttendancePage --> SetSchedule[Set Company Time-In and Time-Out Schedule]
    SetSchedule --> SelectDate[Choose Date and Department]
    SelectDate --> EnterAttendance[Enter Present, Late, Absent or Excused]
    EnterAttendance --> SaveAttendance{Time-In Is Present or Late?}
    SaveAttendance -->|Yes| CompareTime[Compare Time-In with Schedule]
    CompareTime --> SetAttendanceStatus[Set Status to Present or Late]
    SetAttendanceStatus --> SaveAttendanceRecord[(Save Attendance Log)]
    SaveAttendance -->|No| SaveAttendanceRecord
    SaveAttendanceRecord --> ComputeAttendance[Calculate Monthly Attendance and Punctuality]

    HRAction -->|Manage KPI| KPIPage[Open KPI Management]
    KPIPage --> SelectPeriod[Select Employee or Bulk Employee Group]
    SelectPeriod --> SelectMonth[Select Review Month]
    SelectMonth --> KPIType{Select KPI Type}

    KPIType -->|Productivity| EnterWorkload[Enter Target Workload and Completed Workload]
    EnterWorkload --> ComputeProductivity[Completed divided by Target multiplied by 100]
    ComputeProductivity --> CapProductivity{Result Greater than 100?}
    CapProductivity -->|Yes| Set100[Set Productivity Score to 100]
    Set100 --> SaveKPI
    CapProductivity -->|No| SaveKPI

    KPIType -->|Work Quality, Efficiency or Timeliness| EnterManualScore[Enter Score from 0 to 100]
    EnterManualScore --> SaveKPI

    ComputeAttendance --> SaveKPI
    SaveKPI[(Save or Update KPI Record)] --> CombineScores[Collect Scores for the Selected Month]
    CombineScores --> ApplyWeights[Apply Weighted Overall Score]

    ApplyWeights --> WeightCalculation["Productivity 15% + Efficiency 10% + Work Quality 25% + Timeliness 20% + Attendance 20% + Punctuality 10%"]
    WeightCalculation --> MissingCheck{Any KPI Score Missing?}
    MissingCheck -->|Yes| CountZero[Count Missing Score as Zero]
    CountZero --> CalculateOverall[Calculate Overall Score]
    MissingCheck -->|No| CalculateOverall

    CalculateOverall --> OverallRange{Overall Score Range}
    OverallRange -->|90 to 100| Outstanding[Outstanding]
    OverallRange -->|80 to 89| VerySatisfactory[Very Satisfactory]
    OverallRange -->|70 to 79| Satisfactory[Satisfactory]
    OverallRange -->|50 to 69| NeedsImprovement[Needs Improvement]
    OverallRange -->|Below 50| Unsatisfactory[Unsatisfactory]

    Outstanding --> DisplayDashboards[Update HR, Manager and Employee Dashboards]
    VerySatisfactory --> DisplayDashboards
    Satisfactory --> DisplayDashboards
    NeedsImproprovement --> DisplayDashboards
    Unsatisfactory --> DisplayDashboards

    DisplayDashboards --> ReportAction{Generate Report?}
    ReportAction -->|Yes| SelectReport[Select Employee Report or Summary Report]
    SelectReport --> GeneratePDF[Generate PDF Report]
    GeneratePDF --> EndHR([End])
    ReportAction -->|No| EndHR
```

## EMSYNC User Login Flowchart

```mermaid
flowchart TD
    Start([START]) --> OpenLogin[Open EMSYNC Login Page]
    OpenLogin --> EnterCredentials[/Enter Email and Password/]
    EnterCredentials --> SubmitLogin[Submit Login Request]
    SubmitLogin --> CheckAttempts{Too Many Failed Attempts?}

    CheckAttempts -->|Yes| AccountLocked[Temporarily Rate-Limit Login]
    AccountLocked --> ShowLockedError[/Show Please Try Again Message/]
    ShowLockedError --> EndLocked([END])

    CheckAttempts -->|No| ValidateCredentials[Validate Email and Password]
    ValidateCredentials --> CredentialsValid{Credentials Valid?}

    CredentialsValid -->|No| FailedAttempt[Record Failed Login Attempt]
    FailedAttempt --> FiveAttempts{Reached 5 Failed Attempts?}
    FiveAttempts -->|Yes| AccountLocked
    FiveAttempts -->|No| InvalidMessage[/Show Invalid Login Message/]
    InvalidMessage --> Retry[/Retry Login/]
    Retry --> EnterCredentials

    CredentialsValid -->|Yes| CheckRole{User Selected a Role?}
    CheckRole -->|No| CreateSession[Create Authenticated Session]
    CheckRole -->|Yes| RoleMatches{Selected Role Matches Account Role?}

    RoleMatches -->|No| RoleError[/Show Selected Role Does Not Match Account/]
    RoleError --> FailedAttempt

    RoleMatches -->|Yes| CreateSession
    CreateSession --> DetermineRole{Check Account Role}

    DetermineRole -->|HR| HRDashboard[/Access HR Dashboard/]
    DetermineRole -->|Manager| ManagerDashboard[/Access Manager Dashboard/]
    DetermineRole -->|Employee| EmployeeDashboard[/Access Employee Dashboard/]

    HRDashboard --> HRPages[Departments, Employees, KPI and Attendance]
    ManagerDashboard --> ManagerPages[Team and Employee Performance]
    EmployeeDashboard --> EmployeePages[Personal KPI, Attendance and Feedback]

    HRPages --> LoginSuccess([END])
    ManagerPages --> LoginSuccess
    EmployeePages --> LoginSuccess
```

## EMSYNC Data Input Flowchart

```mermaid
flowchart TD
    Start([START]) --> OpenEntry[Open EMSYNC Data Entry Page]
    OpenEntry --> SelectEntry{Select Data to Enter}

    SelectEntry -->|Employee Account| EmployeeForm[/Enter Employee Information/]
    EmployeeForm --> EnterAccountData[Enter Name, Email, Password, Role and Department]

    EnterAccountData --> ValidateAccount{Is Employee Data Valid?}
    ValidateAccount -->|No| AccountError[/Show Validation Error and Check Input/]
    AccountError --> ReturnAccount[Return to Employee Entry Form]
    ReturnAccount --> EnterAccountData

    ValidateAccount -->|Yes| CheckEmail{Email Already Exists?}
    CheckEmail -->|Yes| AccountError
    CheckEmail -->|No| HashPassword[Hash Password]
    HashPassword --> SaveUser[(Save Account in users Table)]

    SelectEntry -->|Attendance| AttendanceForm[/Enter Attendance Information/]
    AttendanceForm --> EnterAttendanceData[Enter Date, Status, Time-In and Time-Out]
    EnterAttendanceData --> ValidateAttendance{Is Attendance Data Valid?}

    ValidateAttendance -->|No| AttendanceError[/Show Validation Error and Check Input/]
    AttendanceError --> ReturnAttendance[Return to Attendance Entry Form]
    ReturnAttendance --> EnterAttendanceData

    ValidateAttendance -->|Yes| CompareSchedule{Status Is Present or Late?}
    CompareSchedule -->|Yes| ComputeStatus[Compare Time-In with Company Schedule]
    ComputeStatus --> SaveAttendance[(Save Attendance in attendance_logs Table)]
    CompareSchedule -->|No| SaveAttendance

    SelectEntry -->|KPI| KPIForm[/Enter KPI Information/]
    KPIForm --> EnterKPIData[Select Employee and Review Month]
    EnterKPIData --> SelectKPIType{Select KPI Type}

    SelectKPIType -->|Productivity| EnterWorkload[Enter Target and Completed Workload]
    EnterWorkload --> ValidateWorkload{Target and Completed Data Valid?}
    ValidateWorkload -->|No| KPIError[/Show Validation Error and Check Input/]
    KPIError --> ReturnKPI[Return to KPI Entry Form]
    ReturnKPI --> EnterWorkload

    ValidateWorkload -->|Yes| ComputeProductivity[Completed divided by Target multiplied by 100]
    ComputeProductivity --> CapScore{Score Greater than 100?}
    CapScore -->|Yes| SetCap[Set Productivity Score to 100]
    SetCap --> SaveKPI[(Save KPI Record)]
    CapScore -->|No| SaveKPI

    SelectKPIType -->|Work Quality, Efficiency or Timeliness| EnterScore[Enter Score from 0 to 100]
    EnterScore --> ValidateKPIScore{Is Score from 0 to 100?}
    ValidateKPIScore -->|No| KPIError
    ValidateKPIScore -->|Yes| SaveKPI

    SaveUser --> EntryComplete[Confirm Account Saved]
    SaveAttendance --> EntryComplete
    SaveKPI --> EntryComplete
    EntryComplete --> End([END])
```

## EMSYNC KPI Computation Flowchart

```mermaid
flowchart TD
    Start([START]) --> SelectEmployee[/Select Employee and Review Month/]
    SelectEmployee --> RetrieveData[(Retrieve KPI Records and Attendance from Database)]

    RetrieveData --> RetrieveManual[Retrieve Productivity, Work Quality, Efficiency and Timeliness]
    RetrieveManual --> ComputeProductivity{Productivity Data Complete?}

    ComputeProductivity -->|Yes| ProductivityFormula["Productivity = Completed Workload / Target Workload x 100"]
    ComputeProductivity -->|No| MissingScore[Set Missing Score to 0]
    ProductivityFormula --> ProductivityCap{Productivity Greater than 100?}
    MissingScore --> MissingProductivity[Record Missing Productivity]
    ProductivityCap -->|Yes| SetProductivity100[Set Productivity to 100]
    ProductivityCap -->|No| KeepProductivity[Keep Computed Productivity]
    SetProductivity100 --> WorkQuality
    KeepProductivity --> WorkQuality
    MissingProductivity --> WorkQuality

    WorkQuality[Retrieve Work Quality Score]
    Efficiency[Retrieve Efficiency Score]
    Timeliness[Retrieve Timeliness Score]
    WorkQuality --> Efficiency
    Efficiency --> Timeliness

    Timeliness --> RetrieveAttendance[(Retrieve Attendance Logs for the Selected Month)]
    RetrieveAttendance --> AttendanceFormula["Attendance = Present or Late Days / Total Logged Days x 100"]
    RetrieveAttendance --> PunctualityFormula["Punctuality = Present Days / Present or Late Days x 100"]

    AttendanceFormula --> AttendanceComplete{Attendance Logs Available?}
    PunctualityFormula --> PunctualityComplete{Present or Late Logs Available?}

    AttendanceComplete -->|Yes| KeepAttendance[Keep Attendance Score]
    AttendanceComplete -->|No| ZeroAttendance[Set Attendance to 0]
    PunctualityComplete -->|Yes| KeepPunctuality[Keep Punctuality Score]
    PunctualityComplete -->|No| ZeroPunctuality[Set Punctuality to 0]

    KeepAttendance --> Combine
    ZeroAttendance --> Combine
    KeepPunctuality --> Combine
    ZeroPunctuality --> Combine

    Combine[Combine Productivity, Work Quality, Efficiency, Timeliness, Attendance and Punctuality]
    Combine --> MissingManual{Any Manual KPI Missing?}
    MissingManual -->|Yes| CountMissing[Count Missing KPI as 0]
    MissingManual -->|No| ApplyWeights
    CountMissing --> ApplyWeights

    ApplyWeights["Overall = Productivity x 15% + Efficiency x 10% + Work Quality x 25% + Timeliness x 20% + Attendance x 20% + Punctuality x 10%"]
    ApplyWeights --> RoundScore[Round Overall Score to Two Decimal Places]

    RoundScore --> IsComplete{KPI Computation Complete?}
    IsComplete -->|No| ReturnInput[/Return to Data Entry and Correct Missing or Invalid Data/]
    ReturnInput --> SelectEmployee

    IsComplete -->|Yes| SaveResult[(Store or Update Monthly KPI Result)]
    SaveResult --> OverallRange{Determine Overall Rating}

    OverallRange -->|90 to 100| Outstanding[Outstanding]
    OverallRange -->|80 to 89| VerySatisfactory[Very Satisfactory]
    OverallRange -->|70 to 79| Satisfactory[Satisfactory]
    OverallRange -->|50 to 69| NeedsImprovement[Needs Improvement]
    OverallRange -->|Below 50| Unsatisfactory[Unsatisfactory]

    Outstanding --> Display[Display Score on HR, Manager and Employee Dashboards]
    VerySatisfactory --> Display
    Satisfactory --> Display
    NeedsImprovement --> Display
    Unsatisfactory --> Display

    Display --> Report{Generate Report?}
    Report -->|Yes| GenerateReport[Generate Employee or Department Summary PDF]
    GenerateReport --> End([END])
    Report -->|No| End
```

## EMSYNC Report Generation Flowchart

```mermaid
flowchart TD
    Start([START]) --> OpenReports[Open EMSYNC Dashboard or Employee Profile]
    OpenReports --> SelectReport[/Select Employee Report or Department Summary Report/]

    SelectReport --> SelectFilters{Choose Report Filters}
    SelectFilters -->|Employee Report| ChooseEmployee[Choose Employee and Month]
    SelectFilters -->|Summary Report| ChooseSummary[Choose Month and Optional Department]

    ChooseEmployee --> RetrieveEmployee[(Retrieve Employee, KPI Records and Attendance)]
    ChooseSummary --> RetrieveEmployees[(Retrieve Employees, KPI Records and Attendance)]

    RetrieveEmployee --> ComputeEmployeeScores[Compute Six KPI Scores and Overall Rating]
    RetrieveEmployees --> FilterEmployees[Filter Employees by Department and Month]
    FilterEmployees --> ComputeSummaries[Compute Each Employee Overall Score]
    ComputeSummaries --> ComputeAverage[Compute Department or Company Average]
    ComputeAverage --> BuildBreakdown[Compute Average by Six KPI Indicators]

    ComputeEmployeeScores --> BuildVisualizations[Generate KPI Cards and Three-Month Trend Chart]
    BuildBreakdown --> BuildVisualizations

    BuildVisualizations --> GeneratePDF[Generate PDF Report]
    GeneratePDF --> EmployeePDF[Employee Performance Report]
    GeneratePDF --> SummaryPDF[Department or Company Summary Report]

    EmployeePDF --> Download[/Display or Download PDF/]
    SummaryPDF --> Download
    Download --> ReportReady{Report Generated Successfully?}

    ReportReady -->|No| ReportError[/Show PDF Generation Error/]
    ReportError --> OpenReports

    ReportReady -->|Yes| End([END])
```

## EMSYNC Data Flow Diagram - Level 0

```mermaid
flowchart LR
    HR[HR Personnel]
    Manager[Manager / Supervisor]
    Employee[Employee]

    EMSYNC((EMSYNC Employee\nManagement and KPI\nMonitoring System))

    DB[(MySQL Database)]

    HR -->|Login, Account, Department and Attendance Data| EMSYNC
    HR -->|Monthly KPI Scores and Workload Data| EMSYNC
    EMSYNC -->|Employee, KPI, Attendance, Report and Feedback Results| HR

    Manager -->|Login and Department Employee Feedback| EMSYNC
    EMSYNC -->|Team Performance, KPI and Attendance Information| Manager

    Employee -->|Login, Profile and Password Updates| EMSYNC
    EMSYNC -->|Personal Dashboard, KPI, Attendance and Feedback| Employee

    EMSYNC <-->|Users, Departments, Attendance, KPI and Feedback Records| DB
```

## EMSYNC Data Flow Diagram - Level 1

```mermaid
flowchart LR
    HR[HR Personnel]
    Manager[Manager / Supervisor]
    Employee[Employee]

    subgraph EMSYNC_SYSTEM["EMSYNC Level 1 Processes"]
        P11((1.1<br/>Account and<br/>Login Processing))
        P12((1.2<br/>Department and<br/>Employee Management))
        P13((1.3<br/>Attendance<br/>Processing))
        P14((1.4<br/>KPI Encoding and<br/>Computation))
        P15((1.5<br/>Feedback and<br/>Profile Processing))
        P16((1.6<br/>Dashboard and<br/>Report Generation))
    end

    D1[(D1: Users and<br/>Departments)]
    D2[(D2: Attendance Logs<br/>and Schedule)]
    D3[(D3: KPI Indicators<br/>and Records)]
    D4[(D4: Performance<br/>Notes and Feedback)]
    D5[(D5: Sessions and<br/>Notification Reads)]

    HR -->|Login Credentials and Role| P11
    P11 -->|Authenticated HR Session| HR
    P11 -->|Account Credential Data| D1
    D1 -->|User and Role Validation| P11

    HR -->|Employee and Department Information| P12
    P12 -->|Validated Account Details| D1
    D1 -->|Employee and Department Records| P12
    P12 -->|Account Status and Department Data| HR

    HR -->|Date, Attendance Status and Work Times| P13
    P13 <-->|Schedule and Attendance Records| D2
    D2 -->|Monthly Attendance Logs| P14
    P13 -->|Attendance Confirmation| HR

    HR -->|Employee, Month, KPI Score or Workload| P14
    P14 <-->|KPI Definitions and Records| D3
    D3 -->|Manual and Computed KPI Scores| P14
    D1 -->|Employee Identity and Department| P14
    D2 -->|Attendance and Punctuality Data| P14
    P14 -->|Calculated Overall KPI and Status| P16

    Manager -->|Login and Department Feedback| P11
    P11 -->|Manager Session and Department Scope| Manager
    Manager -->|Performance Feedback for Department Employee| P15
    P15 -->|Feedback Record| D4
    D4 -->|Existing Feedback| P15
    P15 -->|Feedback Confirmation| Manager

    Employee -->|Login and Profile Updates| P11
    P11 -->|Employee Session| Employee
    Employee -->|Name, Photo or Password Update| P15
    P15 <-->|Profile Information| D1
    P15 -->|Profile Update Confirmation| Employee

    P16 -->|Personal Performance Dashboard| Employee
    P16 -->|Team KPI Report and Dashboard| Manager
    P16 -->|HR Dashboard and Employee Summaries| HR
    P16 -->|Employee or Department Summary PDF| HR

    P11 <-->|Active Sessions| D5
    P16 -->|Report and Notification Data| D5
    D5 -->|Unread or Pending Items| P16
```
