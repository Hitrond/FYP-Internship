# FYP Internship System — User Acceptance Testing

Date: __________  
Tester: __________  
Build/commit: __________  
Environment: Docker / localhost

Mark each item **Pass**, **Fail**, or **N/A** and record evidence or notes.

## Student

- [ ] Login and update profile.
- [ ] Add, edit, and remove company applications.
- [ ] Upload and privately download an offer letter.
- [ ] Generate or upload a resume and cover letter.
- [ ] Submit a placement with valid 16-week dates.
- [ ] Confirm exactly 16 weekly logbooks are generated.
- [ ] Submit and edit an open or rejected logbook.
- [ ] Record Present, Leave, MC, and Public Holiday attendance.
- [ ] Confirm MC requires a medical certificate.
- [ ] Request an extension for an overdue week.
- [ ] Upload the final report and clearance form.

## Academic Mentor

- [ ] View only assigned students and their applications.
- [ ] Approve or reject placement submissions.
- [ ] Review all 16 generated logbook weeks.
- [ ] Review attendance alerts and rejected attendance.
- [ ] Approve or reject extension requests.
- [ ] View submitted supervisor evaluations.
- [ ] Calculate Pass/Fail and export the result CSV.

## Industrial Supervisor

- [ ] Verify the generated supervisor account.
- [ ] View only assigned students.
- [ ] Approve a logbook with verified hours, signature, and stamp.
- [ ] Reject a logbook with mandatory feedback.
- [ ] Submit and lock midterm and final evaluations.
- [ ] Complete final company sign-off.

## Administrator

- [ ] Create and edit users.
- [ ] Create, activate, close, and archive semesters.
- [ ] Assign students to Academic Mentors.
- [ ] Check system statistics, notifications, and alerts.
- [ ] Filter cohort progress by semester.
- [ ] Export institutional progress reports.

## Deadlines

- [ ] Future weeks remain scheduled.
- [ ] Current weeks open automatically.
- [ ] Missed deadlines become overdue and locked.
- [ ] Approved extensions reopen the affected week.
- [ ] Expired extensions lock the week again.

## Security

- [ ] Students cannot access another student’s files.
- [ ] Supervisors cannot access unassigned students.
- [ ] Mentors cannot access students outside their cohort.
- [ ] Non-admin users receive `403` on admin-only pages.
- [ ] Private documents cannot be opened without authorization.

## Validation

- [ ] Reject invalid placement and attendance dates.
- [ ] Reject unsupported or oversized files.
- [ ] Reject MC attendance without evidence.
- [ ] Prevent duplicate weekly logbooks.
- [ ] Require remarks when verified hours are reduced.
- [ ] Require signature and stamp for supervisor approval.
- [ ] Repeated placement approval does not duplicate accounts or weeks.
- [ ] Finalization is blocked until every requirement is complete.

## UI and devices

- [ ] Test desktop, tablet, and mobile widths.
- [ ] Test navigation, dropdowns, and keyboard focus.
- [ ] Test tables, modals, forms, and validation messages.
- [ ] Test long names, empty states, and a large cohort.

## Final regression

```powershell
docker compose exec laravel.test composer test -- --compact
npm run build
docker compose ps
```

- [ ] Automated suite passes.
- [ ] Production assets build successfully.
- [ ] Laravel and PostgreSQL containers report healthy.
- [ ] No new application errors appear in `docker compose logs`.

Sign-off: ____________________  
Date: ____________________
