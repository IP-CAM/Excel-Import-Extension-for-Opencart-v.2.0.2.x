Βήματα για την σωστή λειτουργία με το OpenCart 2.0.2

1.  Εισαγωγή του φακέλου import στον admin directory του OpenCart. 
2.  Ορίστε το prefix της βάσης δεδομένων στο αρχείο system.php 
    
- Εφόσον επιθυμείτε τον authentication έλεγχο τότε η εισαγωγή γίνεται 
  στον admin directory και ορίστε την μεταβλητή OC_AUTHENTICATION=TRUE.
- Οι εικόνες που ορίζεται στα αρχεία excel πρέπει να βρίσκονται ήδη 
  στους φακέλους "image/catalog/categories" για τις εικόνες των κατηγοριών
  και "image/catalog/products" για αυτές των προϊόντων