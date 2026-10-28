# Contribution Rules

## Before coding

1. Confirm that you are inside `C:\xampp\htdocs\e-voting`.
2. Check out your assigned branch.
3. Pull the latest changes from `main` and resolve conflicts before beginning work.
4. Read `README.md` and coordinate any shared-file changes with the affected group.

## Change discipline

- One focused feature or fix per commit.
- Use Laravel migrations for every schema change; do not manually alter the shared database as a substitute for a migration.
- Do not rename or edit another group's migration after it is shared. Create a new migration instead.
- Use Bootstrap 5 classes and the Vite assets in `resources/css/app.css` and `resources/js/app.js`. Do not introduce Tailwind CSS.
- Keep secrets, local logs, generated files, and database dumps out of Git.

## E-voting safety review

Changes affecting eligibility, vote casting, vote counting, result publication, roles, or audit data need a second group member to review them. A review must confirm that a voter cannot submit a duplicate vote and that the change does not reveal ballot choices.
