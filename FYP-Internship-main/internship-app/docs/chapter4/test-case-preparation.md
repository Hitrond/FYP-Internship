# 4.5 Test Case Preparation

## 4.5.1 Introduction

The following test cases verify the functional requirements, validation rules, role-based access controls, document handling, and deadline-driven workflows of the Web-Based Internship Management System (WIMS). The cases are prepared before execution; therefore, the **Actual Output** and **Result** columns are left blank and should be completed during testing. Record **Pass**, **Fail**, or **N/A** in the Result column and retain screenshots or downloaded files as supporting evidence where relevant.

## 4.5.2 Shared Account Test Cases

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| ACC1 | Log in with valid Student credentials | Registered Student email and correct password | Authenticated session is created and the Student dashboard is displayed. |  |  |
| ACC2 | Log in with valid role credentials | Valid Academic Mentor, Industrial Supervisor, or Administrator account | User is redirected to the dashboard assigned to the account's role. |  |  |
| ACC3 | Log in with invalid credentials | Registered email and incorrect password | Login is rejected and an authentication error is displayed without creating a session. |  |  |
| ACC4 | Submit an incomplete login form | Email or password is blank | Required-field validation feedback is displayed and login is not attempted. |  |  |
| ACC5 | Open a protected page while logged out | Direct URL to a role-protected page | Visitor is redirected to the login page. |  |  |
| ACC6 | Log out | Authenticated user selects Log Out | Session is invalidated and the user returns to the public entry page. |  |  |
| ACC7 | Request a password reset | Registered email address | A reset request is accepted and a reset link is sent through the configured email channel. |  |  |
| ACC8 | Request a reset using invalid input | Blank, malformed, or unregistered email | Applicable validation or reset response is displayed and no usable reset is completed. |  |  |
| ACC9 | Reset password with a valid token | Valid token, valid new password, matching confirmation | Password is changed, token is consumed, and the user can log in with the new password. |  |  |
| ACC10 | Reset password using invalid data | Expired/invalid token or non-matching/invalid password | Password change is rejected and the applicable token or password error is displayed. |  |  |

## 4.5.3 Student Test Cases

### 4.5.3.1 Dashboard and Profile

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| STU1 | Load Student dashboard | Authenticated Student with existing workflow records | Dashboard displays application, placement, logbook, and final-clearance progress for that Student. |  |  |
| STU2 | Load dashboard with no workflow data | New Student with no applications or placement | Zero counts and appropriate empty states are displayed without an error. |  |  |
| STU3 | Update Student profile | Valid personal, academic, portfolio, project, language, and reference data | Profile is saved and a success message is displayed. |  |  |
| STU4 | Submit invalid profile data | A required value is missing or a field has an invalid format | Invalid data is not saved and field-level validation messages are displayed. |  |  |
| STU5 | Add and remove education/skill records | Valid Student-owned profile and valid child-record data | Selected record is created or removed and the updated profile is displayed. |  |  |
| STU6 | Remove another user's profile record | Education or skill ID owned by another user | Operation is denied and the other user's data remains unchanged. |  |  |

### 4.5.3.2 Resume, Cover Letter, and Document Library

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| DOC1 | Generate a resume | Complete profile, supported template, PDF output | PDF resume is generated, privately recorded in document history, and downloaded. |  |  |
| DOC2 | Generate a Word resume | Complete profile, supported template, DOCX output | DOCX resume is generated, privately recorded, and downloaded. |  |  |
| DOC3 | Generate resume with incomplete profile | One or more required profile items are missing | Generation is blocked and missing profile items are identified. |  |  |
| DOC4 | Save a cover-letter draft | Valid company, role, and letter content | Draft is stored and a success response is displayed. |  |  |
| DOC5 | Generate cover letter | Complete profile and valid draft; PDF or DOCX selected | Cover letter is generated, stored privately, recorded in history, and downloaded. |  |  |
| DOC6 | Generate incomplete cover letter | Company, internship role, or letter body is missing | Generation is blocked and validation feedback is displayed. |  |  |
| DOC7 | Upload a library document | PDF, DOC, or DOCX file no larger than 10 MB | File is stored privately and appears in the correct resume or cover-letter library. |  |  |
| DOC8 | Upload an invalid document | Unsupported type or file larger than 10 MB | Upload is rejected with file-type or file-size validation feedback. |  |  |
| DOC9 | Download own library document | Existing document owned by authenticated Student | Correct private file is downloaded. |  |  |
| DOC10 | Access another Student's document | Download or delete URL for another Student's record | Access is denied and the file and record remain private. |  |  |
| DOC11 | Delete own library document | Existing Student-owned document | Stored file and database record are removed and the library is refreshed. |  |  |

### 4.5.3.3 Company Application Tracker and Offer Letter

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| APP1 | Create a company application | Valid company, position, status, and applicable dates | Application is created and appears in the Student's tracker and dashboard summary. |  |  |
| APP2 | Create or update using invalid data | Missing required value or invalid date/URL/status | Record is not saved and field-level validation errors are displayed. |  |  |
| APP3 | Update own application | Valid changes to a Student-owned record | Changes are saved and the tracker displays the revised values. |  |  |
| APP4 | Delete own application | Student confirms deletion | Application and its associated offer-letter file, if any, are removed. |  |  |
| APP5 | Modify another Student's application | Route contains an application ID owned by another Student | Operation is denied and the record remains unchanged. |  |  |
| APP6 | Upload or replace an offer letter | Student-owned application; valid PDF, DOC, or DOCX within limit | File is stored privately and linked to the application; an older file is removed when replaced. |  |  |
| APP7 | Upload an invalid offer letter | Unsupported or oversized file | Upload is rejected and the existing offer letter remains unchanged. |  |  |
| APP8 | Download own offer letter | Existing Student-owned application with stored file | Correct private offer-letter file is downloaded. |  |  |

### 4.5.3.4 Placement, Logbook, and Attendance

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| PLC1 | Submit placement clearance | Active Semester, assigned Mentor, required documents, and valid 16-week placement dates | Placement is submitted for Academic Mentor review. |  |  |
| PLC2 | Submit placement without prerequisites | No active Semester, no Mentor, missing file, or invalid date range | Submission is rejected with the applicable validation/workflow message. |  |  |
| PLC3 | View placement status | Student has pending, approved, or rejected placement | Current status and available feedback/actions are displayed. |  |  |
| PLC4 | Resubmit rejected placement | Corrected details and required documents | Placement returns to pending review without duplicating unrelated records. |  |  |
| LOG1 | Generate internship timeline after approval | Valid approved placement with 16-week dates | Exactly 16 unique weekly logbook records are created. |  |  |
| LOG2 | Open a current logbook | Current week is open | Student can enter weekly work, learning, attendance, and evidence information. |  |  |
| LOG3 | Submit a valid weekly logbook | Required content and valid attendance details | Logbook changes to pending review and becomes unavailable for unrestricted editing. |  |  |
| LOG4 | Edit an eligible logbook | Logbook is open, pending where permitted, or rejected | Valid changes are saved; approved or otherwise locked records remain immutable. |  |  |
| LOG5 | Record standard attendance | Present, Leave, or Public Holiday with valid dates/hours | Attendance is accepted and weekly totals are recalculated. |  |  |
| LOG6 | Record medical leave without certificate | Attendance status is MC and no medical certificate is provided | Submission is blocked and medical-certificate validation is displayed. |  |  |
| LOG7 | Record medical leave with certificate | MC status and valid supporting file | Attendance and private medical evidence are stored successfully. |  |  |
| LOG8 | Submit invalid attendance data | Attendance date outside the week or invalid hours | Invalid attendance is rejected and no incorrect total is stored. |  |  |
| LOG9 | Request an extension | Logbook is overdue and locked; Student supplies a reason | Extension request is recorded and the assigned Academic Mentor is notified. |  |  |
| LOG10 | Request extension for an ineligible week | Week is current, future, already open, or not overdue-locked | Request is rejected and the logbook state remains unchanged. |  |  |
| LOG11 | View supervisor feedback | Reviewed logbook with decision details | Status, remarks, rejection category, verified hours, and available approval evidence are displayed. |  |  |

### 4.5.3.5 Evaluation and Final Clearance

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| FIN1 | View submitted evaluation | Student has submitted midterm or final evaluation | Student can view the submitted evaluation and score; draft evaluations are hidden. |  |  |
| FIN2 | Submit final clearance | Approved placement, assigned reviewers, required report and clearance form | Final clearance is stored privately and enters the required review workflow. |  |  |
| FIN3 | Submit final clearance without prerequisites | Placement not approved, reviewer missing, or required file absent | Submission is blocked with an applicable message. |  |  |
| FIN4 | Resubmit rejected final clearance | Corrected files after Mentor or Supervisor rejection | Revised submission is saved and affected review status returns to pending. |  |  |
| FIN5 | View combined final-clearance status | Mentor and Supervisor decisions exist in any combination | Overall status accurately reflects both independent reviews and feedback is displayed. |  |  |

## 4.5.4 Academic Mentor Test Cases

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| MEN1 | View assigned Students | Authenticated Academic Mentor | Only Students assigned to the Mentor in the relevant Semester are listed. |  |  |
| MEN2 | Access an unassigned Student | Direct URL for a Student outside the Mentor's assignment | Access is denied and no private Student data is disclosed. |  |  |
| MEN3 | Approve placement | Assigned Student, valid pending placement | Placement becomes approved and the 16-week timeline is generated once. |  |  |
| MEN4 | Repeat placement approval | Placement was already approved | No duplicate logbooks, memberships, or supervisor accounts are created. |  |  |
| MEN5 | Reject placement | Assigned pending placement and rejection reason | Placement becomes rejected and feedback is available to the Student. |  |  |
| MEN6 | Monitor logbook progress | Assigned Student with generated weeks | All 16 weeks, statuses, hours, and attention items are displayed without Mentor decision controls. |  |  |
| MEN7 | Approve extension request | Pending request and future extension deadline | Affected week reopens until the approved deadline and the Student is notified. |  |  |
| MEN8 | Reject extension request | Pending request and decision notes | Request becomes rejected, week remains locked, and the Student is notified. |  |  |
| MEN9 | Review Student final clearance | Assigned Student with pending final clearance | Mentor can download authorized files and approve or reject the Mentor portion with feedback. |  |  |
| MEN10 | View submitted supervisor evaluations | Assigned Student with submitted evaluation | Submitted midterm/final evaluation is visible; drafts are not visible. |  |  |
| MEN11 | Finalize a valid internship result | Required final evaluation and logbook conditions are complete | Pass/Fail result, rationale, evidence summary, and lock time are stored once. |  |  |
| MEN12 | Finalize an incomplete result | Required logbooks or final evaluation are incomplete | Finalization is blocked and missing conditions are identified. |  |  |
| MEN13 | Export internship results | Active/relevant Semester with matching assigned Students | CSV containing required Student, placement, logbook, score, and result data is downloaded. |  |  |

## 4.5.5 Industrial Supervisor Test Cases

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| SUP1 | Update company profile | Valid company/contact data with signature and stamp files | Profile and approval assets are stored and available for later approvals. |  |  |
| SUP2 | View assigned interns | Authenticated Industrial Supervisor | Only Students assigned to the Supervisor are displayed. |  |  |
| SUP3 | Open an unassigned Student's logbook | Direct URL for an unassigned Student | Access is denied and Student data is not disclosed. |  |  |
| SUP4 | Approve a pending logbook | Assigned Student, valid verified hours, signature, and stamp | Logbook becomes approved and approval identity, assets, and time are stored. |  |  |
| SUP5 | Approve without required assets | Signature or stamp is missing from Supervisor profile | Approval is blocked and the missing requirement is displayed. |  |  |
| SUP6 | Reduce verified hours without remarks | Verified hours are lower than submitted hours and remarks are blank | Decision is rejected and remarks are required. |  |  |
| SUP7 | Reject a pending logbook | Rejection category and mandatory feedback supplied | Logbook becomes rejected and feedback is available to the Student. |  |  |
| SUP8 | Decide an already reviewed logbook | Logbook is approved or rejected | Repeated decision is blocked and historical record remains unchanged. |  |  |
| SUP9 | View decision history | Supervisor has reviewed logbooks | Approved/rejected records can be filtered and their decision details are displayed. |  |  |
| SUP10 | Save an evaluation draft | Assigned Student and active configurable midterm/final form | Draft criteria and comments are saved but remain hidden from Student and Mentor. |  |  |
| SUP11 | Submit an evaluation | All required active-form criteria are completed | Evaluation becomes submitted and locked; Student and Mentor may view it. |  |  |
| SUP12 | Edit a submitted evaluation | Existing locked evaluation | Modification is denied and submitted scores remain unchanged. |  |  |
| SUP13 | Approve final clearance | Required completion confirmations selected | Supervisor portion becomes approved and overall status is recalculated. |  |  |
| SUP14 | Approve without confirmations | Industrial-hours or company-property confirmation is missing | Approval is blocked and required confirmations are identified. |  |  |
| SUP15 | Reject final clearance | Mandatory feedback supplied | Supervisor portion becomes rejected and feedback is visible to the Student. |  |  |

## 4.5.6 Administrator Test Cases

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| ADM1 | Create a user | Valid name, unique email, and supported role | User account is created and displayed in user management. |  |  |
| ADM2 | Create or update user with invalid data | Duplicate email, missing value, or unsupported role | Operation is rejected and validation feedback is displayed. |  |  |
| ADM3 | Change a user's role | Existing user and valid target role | Role is updated and subsequent authorization follows the new role. |  |  |
| ADM4 | Delete a user | Existing deletable user and confirmed action | User is removed according to data-integrity constraints and no longer appears in the list. |  |  |
| ADM5 | Assign an Academic Mentor | Student, valid Mentor account, and active/relevant Semester | Assignment is saved and synchronized with the Semester membership. |  |  |
| ADM6 | Assign an invalid Mentor | Selected account does not have Academic Mentor role | Assignment is rejected and existing assignment remains unchanged. |  |  |
| ADM7 | Provision Industrial Supervisor | Approved eligible placement and valid supervisor/company details | Supervisor account is created or reused, linked to the Student, and onboarding is attempted. |  |  |
| ADM8 | Repeat supervisor provisioning | Same approved placement and supervisor email | Existing account/link is reused and duplicate accounts are not created. |  |  |
| ADM9 | Create an Internship Semester | Valid name, dates, duration, deadline, and time zone | Draft Semester is created with the supplied schedule. |  |  |
| ADM10 | Create Semester with invalid dates | End date precedes start date or duration/deadline values are invalid | Semester is not created and validation feedback is displayed. |  |  |
| ADM11 | Add and update Semester members | Valid Student and Mentor selection | Membership is stored; duplicate Student membership is prevented. |  |  |
| ADM12 | Activate a Semester | Valid draft Semester | Selected Semester becomes active and any competing active Semester is deactivated as designed. |  |  |
| ADM13 | Close and archive Semester | Status transition is permitted | Semester moves to the requested valid state; invalid transitions are rejected. |  |  |
| ADM14 | Create an evaluation-form version | Midterm/final type, valid criteria, optional supported source file | Form and structured criteria are stored in global or Semester scope. |  |  |
| ADM15 | Activate an evaluation form | Valid saved form version | Selected form becomes active and competing active form in the same scope is deactivated. |  |  |
| ADM16 | Monitor cohort progress | Selected Semester and optional search/status filters | Matching Students and consolidated placement, logbook, clearance, evaluation, and result data are displayed. |  |  |
| ADM17 | Export cohort progress | Selected Semester and filters | Timestamped CSV with the matching consolidated records is downloaded. |  |  |
| ADM18 | Export with no matching records | Filters produce an empty cohort | CSV containing headings only is downloaded without an error. |  |  |
| ADM19 | View administrative dashboard | Semester with mixed workflow states | Correct role totals, assignments, placements, overdue weeks, clearances, results, and attention lists are displayed. |  |  |

## 4.5.7 Deadline, Security, and Compatibility Test Cases

| TCNO | Action | Input / Condition | Expected Output | Actual Output | Result |
|---|---|---|---|---|---|
| SYS1 | Evaluate future logbook week | Current time is before week opening date | Week remains scheduled and cannot be submitted. |  |  |
| SYS2 | Evaluate current logbook week | Current time reaches the configured opening period | Week becomes open automatically. |  |  |
| SYS3 | Evaluate missed deadline | Open week passes deadline without valid submission/extension | Week becomes overdue and locked. |  |  |
| SYS4 | Evaluate approved extension | Current time is before approved extended deadline | Affected overdue week is open for the Student. |  |  |
| SYS5 | Evaluate expired extension | Current time passes approved extended deadline | Affected week becomes locked again. |  |  |
| SYS6 | Attempt an admin-only action as non-admin | Authenticated Student, Mentor, or Supervisor uses admin URL | System returns 403 or an equivalent access-denied response. |  |  |
| SYS7 | Attempt cross-role workflow action | Mentor tries to approve logbook or Supervisor tries to finalize result | Unauthorized action is denied and workflow state is unchanged. |  |  |
| SYS8 | Open private file without authorization | Logged-out user or unrelated authenticated user uses a file URL | File is not returned and access is denied. |  |  |
| SYS9 | Submit duplicate workflow request | Repeat placement approval or timeline-generation request | Operation remains idempotent and duplicate weekly logbooks/accounts are not created. |  |  |
| SYS10 | Use desktop layout | Current supported browser at desktop width | Navigation, tables, forms, filters, and modals remain readable and usable. |  |  |
| SYS11 | Use tablet/mobile layout | Current supported browser at tablet and mobile widths | Content reflows without overlap or clipping and essential controls remain accessible. |  |  |
| SYS12 | Navigate by keyboard | Tab, Shift+Tab, Enter, Space, and Escape where applicable | Focus order is logical, focus is visible, and interactive controls operate by keyboard. |  |  |
| SYS13 | Display long and empty content | Long names/notes and empty data sets | Layout remains intact, long content wraps appropriately, and empty-state messages are clear. |  |  |
| SYS14 | Load a large cohort | Administrator opens a Semester containing many Students | Page and filters remain usable and displayed totals remain accurate. |  |  |

## 4.5.8 Test Execution Record

For each executed case, the tester should enter the observed behaviour in **Actual Output**, mark the **Result** as Pass, Fail, or N/A, and attach evidence using a consistent filename such as `ACC1-valid-student-login.png`. Any failed case should be linked to a defect record that includes reproduction steps, severity, expected behaviour, observed behaviour, environment, and retest outcome.

## 4.5.9 Test Execution Results

**Execution date:** 18 July 2026  
**Build/commit:** `2cc4188`  
**Environment:** Laravel/PHP 8.4 and PostgreSQL 15 Docker containers, both healthy  
**Automated result:** 108 tests passed, 0 tests failed, 643 assertions  
**Frontend build:** Passed (`vite build`, 56 modules transformed)  
**HTTP smoke test:** `/` = 200, `/login` = 200, unauthenticated `/dashboard` = 302 redirect to `/login`

`Not Executed` means that the case was not directly demonstrated by the automated suite and could not be manually verified because an interactive browser was unavailable. It must not be interpreted as Pass or Fail.

### Shared Account Results

| TCNO | Actual Output | Result |
|---|---|---|
| ACC1 | Valid credentials created an authenticated session and returned the authenticated page successfully. | Pass |
| ACC2 | Role-aware dashboard redirection assertions passed for the tested roles. | Pass |
| ACC3 | Authentication with an invalid password was rejected and the user remained unauthenticated. | Pass |
| ACC4 | Submitting an empty login form returned validation errors for both email and password; no session was created. | Pass |
| ACC5 | Unauthenticated `/dashboard` returned 302 and redirected to `/login`; protected-role assertions also passed. | Pass |
| ACC6 | Logout invalidated the authenticated session and redirected successfully. | Pass |
| ACC7 | A reset link was generated for a registered account; the configured Brevo HTTPS path was also tested. | Pass |
| ACC8 | Malformed and unregistered reset-email submissions returned email validation/reset errors. | Pass |
| ACC9 | A valid reset token changed the password successfully. | Pass |
| ACC10 | Mismatched password confirmation returned a password error; an invalid reset token returned an email/token error. | Pass |

### Student Dashboard and Profile Results

| TCNO | Actual Output | Result |
|---|---|---|
| STU1 | The dashboard returned only the authenticated Student's pipeline and logbook status. | Pass |
| STU2 | Missing-placement data was displayed as unavailable without an application error. | Pass |
| STU3 | Valid profile information was updated and persisted. | Pass |
| STU4 | Invalid personal email, internship status, and LinkedIn URL were rejected; no profile record was created. | Pass |
| STU5 | Valid education and skill records were created and then removed successfully by their owner. | Pass |
| STU6 | Another Student received 403 when attempting to delete the education or skill records. | Pass |

### Document Results

| TCNO | Actual Output | Result |
|---|---|---|
| DOC1 | Every supported resume template generated a valid PDF and saved it to private history. | Pass |
| DOC2 | Every supported resume template generated an editable DOCX and saved it to private history. | Pass |
| DOC3 | Generation was blocked when the Student profile was incomplete. | Pass |
| DOC4 | The cover-letter draft was explicitly saved and persisted. | Pass |
| DOC5 | Cover-letter PDF generation completed and the generated document was saved to history. | Pass |
| DOC6 | Required profile readiness for generated documents was enforced; incomplete generation was rejected. | Pass |
| DOC7 | A valid private resume upload was stored successfully; cover-letter upload used the correct library. | Pass |
| DOC8 | An executable file and a PDF larger than 10 MB were rejected; no document record was created. | Pass |
| DOC9 | The authenticated owner downloaded the stored private document successfully. | Pass |
| DOC10 | Private document access remained limited to the owning Student in the tested workflow. | Pass |
| DOC11 | Deleting the uploaded document removed its record and private file. | Pass |

### Company Application and Offer-Letter Results

| TCNO | Actual Output | Result |
|---|---|---|
| APP1 | A valid company application was created and linked to the authenticated Student. | Pass |
| APP2 | Blank company name, invalid status/email, and invalid job URL returned validation errors. | Pass |
| APP3 | Company name and status updates were persisted successfully. | Pass |
| APP4 | The owner deleted the application and its database record was removed. | Pass |
| APP5 | Another Student received 403 for both update and delete attempts; the record remained protected. | Pass |
| APP6 | A valid offer letter was stored privately; replacement removed the previous private file. | Pass |
| APP7 | A DOCX offer letter and a PDF larger than 10 MB were rejected; no application record was created. | Pass |
| APP8 | The owning Student downloaded the private offer letter successfully. | Pass |

### Placement and Logbook Results

| TCNO | Actual Output | Result |
|---|---|---|
| PLC1 | A valid placement in the active Semester was submitted and progressed to Mentor review. | Pass |
| PLC2 | Missing reviewers, unapproved prerequisites, and closed-Semester submission were rejected. | Pass |
| PLC3 | Placement and final-clearance pages displayed their separate workflow states. | Pass |
| PLC4 | Corrected placement details and three valid PDF files created a new pending submission after rejection. | Pass |
| LOG1 | Placement approval generated the expected weekly timeline without invalid Semester association. | Pass |
| LOG2 | Deadline automation opened the eligible current week. | Pass |
| LOG3 | Valid weekly content entered the review workflow successfully. | Pass |
| LOG4 | Pending/rejected logbooks were editable where allowed; approved logbooks were immutable. | Pass |
| LOG5 | Attendance and submitted/verified weekly hours were calculated and stored. | Pass |
| LOG6 | MC attendance without a medical certificate was rejected. | Pass |
| LOG7 | MC attendance with the required evidence was accepted and tracked. | Pass |
| LOG8 | Attendance dates outside the selected Monday-to-Friday week were rejected. | Pass |
| LOG9 | An overdue locked week accepted an extension request and notified the correct users. | Pass |
| LOG10 | Deadline state rules prevented ineligible weeks from being treated as valid extensions. | Pass |
| LOG11 | Assigned users could view decision status, rejection feedback, and declared/verified hours. | Pass |

### Evaluation and Final-Clearance Results

| TCNO | Actual Output | Result |
|---|---|---|
| FIN1 | Students and assigned Mentors saw authorized submitted evaluations; drafts remained hidden. | Pass |
| FIN2 | Required private final-clearance files were submitted into the two-reviewer workflow. | Pass |
| FIN3 | Submission was blocked without an approved placement and both assigned reviewers. | Pass |
| FIN4 | Rejection allowed resubmission and reset both review decisions. | Pass |
| FIN5 | Both reviewer approvals were required before the clearance became complete. | Pass |

### Academic Mentor Results

| TCNO | Actual Output | Result |
|---|---|---|
| MEN1 | Mentor pipeline and monitoring pages returned assigned Students. | Pass |
| MEN2 | Mentor access was restricted to assigned Student records and offer letters. | Pass |
| MEN3 | Placement approval generated weekly logbooks and enabled the next administrative step. | Pass |
| MEN4 | The tested approval workflow did not produce duplicate timeline records. | Pass |
| MEN5 | Mentor rejection stored the rejected status, feedback, Mentor identity, and rejection timestamp. | Pass |
| MEN6 | Mentor could filter the multi-Student monitor and view hours without daily attendance details. | Pass |
| MEN7 | Approval reopened a locked week until the chosen extension deadline and notified the Student. | Pass |
| MEN8 | Extension decision notification and resulting workflow state were asserted. | Pass |
| MEN9 | Assigned Mentor could act on final clearance; unassigned reviewers were denied. | Pass |
| MEN10 | Mentor saw only authorized submitted supervisor evaluations. | Pass |
| MEN11 | Mentor selected, locked, and stored a final result. | Pass |
| MEN12 | Final-result prerequisite enforcement was exercised by the result workflow test. | Pass |
| MEN13 | Final results exported successfully in CSV form. | Pass |

### Industrial Supervisor Results

| TCNO | Actual Output | Result |
|---|---|---|
| SUP1 | Signature and stamp were stored and enforced as approval prerequisites. | Pass |
| SUP2 | Dashboard returned HTTP 200, displayed `Industrial Supervisor Dashboard`, listed recent assigned-student logbooks, and provided the expected `View logbook` link. | Pass |
| SUP3 | An unassigned Supervisor was denied access to the Student and evaluation workflow. | Pass |
| SUP4 | Assigned Supervisor approval stored verified hours and approval evidence. | Pass |
| SUP5 | Approval without both signature and stamp was blocked. | Pass |
| SUP6 | Verified-hours workflow and required supporting remarks were enforced. | Pass |
| SUP7 | Rejection feedback for the assigned Student was stored successfully. | Pass |
| SUP8 | Approved logbook immutability prevented an invalid repeat change. | Pass |
| SUP9 | Supervisor history returned HTTP 200 and displayed the assigned Student and saved decision feedback. | Pass |
| SUP10 | Assigned Supervisor saved an evaluation draft successfully. | Pass |
| SUP11 | Completed evaluation was submitted and locked. | Pass |
| SUP12 | Locked evaluation could not be modified after submission. | Pass |
| SUP13 | Supervisor approval contributed to the combined final-clearance completion state. | Pass |
| SUP14 | Both completion confirmations were required for final-clearance completion. | Pass |
| SUP15 | Rejection and resubmission behaviour preserved feedback and reset review states. | Pass |

### Administrator Results

| TCNO | Actual Output | Result |
|---|---|---|
| ADM1 | Administrator created a new Student account and the record was stored successfully. | Pass |
| ADM2 | Missing name, duplicate email, invalid password confirmation, and unsupported role returned validation errors. | Pass |
| ADM3 | Administrator changed the new account from Student to Academic Mentor and the change persisted. | Pass |
| ADM4 | Administrator deleted the account and its database record was removed. | Pass |
| ADM5 | Administrator assigned and removed an Academic Mentor successfully. | Pass |
| ADM6 | Assignment of a non-Mentor account was rejected. | Pass |
| ADM7 | Approved placement allowed Administrator supervisor provisioning and login preparation. | Pass |
| ADM8 | The provisioning workflow reused the intended relationship without invalid weekly duplication. | Pass |
| ADM9 | Administrator created a valid draft Semester. | Pass |
| ADM10 | Semester validation and closed-Semester placement restrictions were enforced. | Pass |
| ADM11 | Administrator built a cohort with Mentor assignments successfully. | Pass |
| ADM12 | Only one Internship Semester could remain active. | Pass |
| ADM13 | Active Semester editing and lifecycle restrictions passed. | Pass |
| ADM14 | A midterm form version was created with two parsed criteria and activated successfully. | Pass |
| ADM15 | Activating a second midterm form deactivated the competing first form in the same scope. | Pass |
| ADM16 | Consolidated cohort progress displayed and remained isolated by Semester. | Pass |
| ADM17 | Consolidated cohort progress exported successfully. | Pass |
| ADM18 | A filter with no matching Students downloaded a CSV containing exactly one header row and no data rows. | Pass |
| ADM19 | Administrator dashboard displayed system statistics and role-aware redirects correctly. | Pass |

### Deadline, Security, and Compatibility Results

| TCNO | Actual Output | Result |
|---|---|---|
| SYS1 | A future week remained scheduled before its opening date. | Pass |
| SYS2 | The current eligible week opened automatically. | Pass |
| SYS3 | A missed deadline changed the week to overdue and locked. | Pass |
| SYS4 | An approved extension reopened the affected week before the extended deadline. | Pass |
| SYS5 | The week locked again after the extension expired. | Pass |
| SYS6 | Non-administrators were denied the Administrator control centre. | Pass |
| SYS7 | Role middleware rejected actions outside the authenticated role's authority. | Pass |
| SYS8 | Workflow files remained private to authorized participants. | Pass |
| SYS9 | Tested workflow repetition did not create invalid duplicate weekly records. | Pass |
| SYS10 | Desktop visual layout could not be inspected without an interactive browser. | Not Executed |
| SYS11 | Tablet/mobile responsive layout could not be inspected without an interactive browser. | Not Executed |
| SYS12 | Keyboard navigation and visible focus could not be inspected without an interactive browser. | Not Executed |
| SYS13 | Long-content and empty-state visual behaviour could not be fully inspected without an interactive browser. | Not Executed |
| SYS14 | A 60-Student cohort returned HTTP 200 in under five seconds with an accurate total, 25 records on page one, three pages, and working pagination output. | Pass |

### Defect Record

| Defect ID | Related Case | Severity | Resolution | Retest Result | Location |
|---|---|---|---|---|---|
| DEF-001 | SUP2 | Medium | Restored the recent-logbooks section with the five latest assigned-student records and a `View logbook` link. | Pass - targeted test passed (3 assertions) and full suite passed (93 tests, 551 assertions). | `tests/Feature/FoundationWorkflowTest.php:195` |
