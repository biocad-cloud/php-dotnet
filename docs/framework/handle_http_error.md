# Handle http error

Sometimes you want to custom the error page for your web app, then you can implements such custom http error page in php.NET framework through these steps:

### 1. Write view file

For an instance example, we want to custom the 404 error page for our web app, so that we should create a html view file for display such error page, please notice that the file name of the custom error page should be the http error code, like ``404.html``, ``429.html``,``500.html``, etc

```html
<!-- file name is 404.html for custom http error page: 404 not found -->
<!DOCTYPE html>
<html lang="en">
<head>
    ${../includes/head.html}
</head>
<body>
    ${../includes/nav.html}
    <div class="container-fluid common_content">
        <div class="main">
            <h1>404 Page Not Found!</h1>
            <blockquote>
                <p>
                    <span style="color:gray;">{$url}</span>
                </p>
            </blockquote>
            <p>We could not find the above page on our servers</p>
            <hr />
            <p>
                {$message}
            </p>
        </div>
    </div>
    ${../includes/footer.html}
</body>
</html>
```

Writing such custom http error page is just like writing the normal web app view file, includes some common view fragment components, and then insert some value placeholder for final page rendering, that's it!

For display the error message and information, we suggested that you should add two preserved variable placeholder ``{$url}`` and ``{$message}``. The ``url`` variable for display the request url and the ``message`` variable for display the user defined error message or some technical information about such http error.

### 2. Config registry

After writing the custom error page html view file, then you should link the view file its container folder to the framework in the configuration file, then the php.NET framework could display your custom http error page. Do such configuration in php.NET framework just simple, open the registry file and then add the registry value:

```php
<?php

return [
    ... other registry value
    # modify or add the registry value
    "RFC7231" => "path/to/the/http/error/page/folder"
];
```

About how to do configuration in php.NET framework, read this document: [**&lt;Configuration and Registry>**](../../docs/framework/registry.md).