Booked UP — CSS-correlated PHP package

Changes made:
1. browsebook.php now loads css/books.css instead of css/browsebook.css.
   The existing books.css already contains the browse/filter selectors.
2. addbook.php no longer uses the unused price-field class; its form controls
   remain covered by css/forms.css.
3. PHP files are provided under their canonical project filenames.
4. CSS files are included under /css using the split stylesheet structure.
5. No database/business logic was changed.

Important:
- header.php continues loading style.css, hero.css and navbar.css because
  existing dashboard/listing/profile pages use shared button styling from
  hero.css.
