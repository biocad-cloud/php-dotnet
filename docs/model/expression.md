# MySql Expression Value


## Helper functions

| function &amp; usage    | expression                | parameter note               | description                           |
|-------------------------|---------------------------|------------------------------|---------------------------------------|
| ``eq($value)``          | = '$value'                | string or number             | left is equals to the given value     |
| ``not_eq($value)``      | <> '$value'               | string or number             | left is not equals to the given value |
| ``gt($value)``          | > '$value'                | string or number             |                                       |
| ``gt_eq($value)``       | >= '$value'               | string or number             |                                       |
| ``lt($value)``          | < '$value'                | string or number             |                                       |
| ``lt_eq($value)``       | <= '$value'               | string or number             |                                       |
| ``like($value)``        | LIKE '$value'             | string with wildcard pattern |                                       |
| ``not_like($value)``    | NOT LIKE '$value'         | string with wildcard pattern |                                       |
| ``between($a, $b)``     | BETWEEN '$a' AND '$b'     | number                       |                                       |
| ``not_between($a, $b)`` | NOT BETWEEN '$a' AND '$b' | number                       |                                       |
| ``in($values)``         | IN (...)                  | string or number             |                                       |
| ``not_in($values)``     | NOT IN (...)              | string or number             |                                       |