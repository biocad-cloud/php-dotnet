# MySQL Debug

There are two debugger method that can display the last mysql query expression in the table model object, and you can use this method for make the debug of your mysql query in the impementation of you web app:

```php
<?php

# get last executed mysql expression text.
Table::getLastMySql($code = false);
# get the error information about the last executed mysql query expression.
Table::getLastMySqlError();
```

+ NOTE: the ``getLastMySql`` method in the table model class accept an optional parameter that could controls of the returned mysql expression string text. If the ``code`` parameter value is ``true``, then a html text with sql code highlight will be returned to the caller, otherwise the sql expression text will be returns in plain text format.

