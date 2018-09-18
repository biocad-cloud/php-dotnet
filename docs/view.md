# HTML user interface view handler

## View api

There are three important user interface view api in the php.NET MVC View class:

```php
<?php

View::Display();
View::Show();
View::Load();
```

The relationships between these View api are:

+ ``View::Display`` is roughly equals to ``View::Show($html_path)``, where the ``$html_path`` parameter can be configed automatic in the ``View::Display()`` api.
+ ``View::Show()`` is roughly equals to ``echo View::Load($html_path)``, where the ``$html_path`` parameter is comes from the function parameter of the ``View::Show()`` api.
+ ``View::Load()`` is the very foundation api for display the user view document. This api running the specific html view template and fragment file reading, view rendering, and cache hits etc.

## Template Rendering 

The placeholder in the user view document, includes:

+ variable tag: ``{$variable}``; For example, there is a placeholder in the view file: ``{$title}``, then you can config in the view api calls: ``["title" => "123"]`` 
+ url tag: ``{<directory>phpfile/controller}``; The directory part can be omit if the controller file is in the document root, for point to a controller which named ``hello`` in ``index.php`` file, then you can write ``{index/hello}``, and it will rendering as ``/index.php?app=hello``, for point to a controller which named hello in ``test/index.php`` file, then you can write ``{<test>index/hello}``, and it will rendering as ``/test/index.php?app=hello``.
+ fragment includes: ``${folder/file}``; If a html document ``footer.html`` is part of the view document, then you can include the footer fragment document by using expression: ``${includes/footer.html}``.
+ foreach loop: ``<foreach @array></foreach>``
+ volist: ``<volist name="variable" id="alias"></volist>``

Example:

1. template file

```html
<title>{$title}_test</title>
<a href="{<test>index/hello}">hello world</a>
<ul>
<foreach @list>
    <li>@list["name"] = @list["value"]</li>
</foreach>
</ul>

${includes/footer.html}
```

2. A footer includes file

```html
<p>This is a test web app</p>
```

3. View display

```php
<?php

View::Display([
    "title" => "2233", 
    "list"  => [
        ["name" => "1+1", "value" => 2],
        ["name" => "2*3", "value" => 6]
    ]
]);
```

4. Template rendering result

```html
<title>2233_test</title>
<a href="/test/index.php?app=hello">hello world</a>
<ul>

    <li>1+1 = 2</li>
    <li>2*3 = 6</li>

</ul>

<p>This is a test web app</p>
```

## Configuration for Views

For load a given user view document, the path value of the html document should be configed in the ``config.php``:

```php
"MVC_VIEW_ROOT" => [
    "index"     => "./html/",
    "biodeepDB" => "./html/biodeepDB/",
    "new_task"  => "./html/new_task/",
    "market"    => "./html/market/"
]
```

If the ``MVC_VIEW_ROOT`` item is missing from the php config file, then a default relative directory path ``./html/`` that will be used. This relative directory path which it means all of the view document is contains in the ``html`` directory, and the directory is under the web site's document root.

If your web app have multiple controller file, then you can specific the seperate view document directory for each controller file, example as above code shows. For a more specific example, like you want using ``<DOCUMENT_ROOT>/html/biodeepDB`` directory for all of the view in ``biodeepDB.php`` file, then you can write a key-value config data in the ``MVC_VIEW_ROOT`` section, like: ``"biodeepDB" => "./html/biodeepDB/"``. The relationship of the ``biodeepDB.php`` controller file and the view document directory is:

```
wwwroot/
  +------biodeepDB.php
  +------html/
           +-----biodeepDB/
                   +-------view1.html
                   +-------view2.html
                   +-------view3.html
```

If the web app in biodeepDB.php source file have the controller function which their names are the same as the html document, then you can using ``View::Display()`` api for display the user interface that defined in the corresponding html files:

```php
<?php

# biodeepDB.php
class App {

    # ./html/biodeepDB/view1.html
    public function view1() {
        View::Display();
    }

    # ./html/biodeepDB/view2.html
    public function view2() {
        echo View::Load("./html/biodeepDB/view2.html");
    }

    public function view3() {
        View::Show("./html/biodeepDB/view3.html");
    }
}
```