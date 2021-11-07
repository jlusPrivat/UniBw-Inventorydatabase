# UniBwM Inventory Database
This inventory database provides these functionalities:

## Requirements:
- PHP 8.0 with php-ldap
- MySQL / MariaDB

## User management
- [ ] Values: ID, RZ-ID, email, valid until, groups
- [ ] Local user / password database
- [ ] UniBw LDAP ID. All users are automatically transferred to local database (with information)
- [ ] Users are automatically removed in configurable intervals (if not used).
- [ ] Groups (administrator, moderator, lender, viewer) each permissive and denying
- [ ] Group Permissions: May lend from inst, May return to inst, May manage users from inst, may configure global settings incl groups and institutes

## Inventory management
- [ ] Synonyms for Manufacturers and Items
- [ ] Values: ID, Barcode, Is-Qty, debit-Qty, Description (MD), Tmp-Notes (MD), Responsible-Person, Institute, ...
- [ ] Import: XLSX with prevalidation based on PHP-template files
- [ ] Import: User defined conversion functions for column contents
- [ ] Import: Identify and overwrite existing items
- [ ] Export: As SQL / CSV / XLSX all items
- [ ] Lending: With duration, who gave out, who received, Barcode-scanner, E-Mail notifications lender / receiver
- [ ] Stocktaking: Barcode / Quantity entry mode