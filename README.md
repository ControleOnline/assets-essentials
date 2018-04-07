[![Build Status](https://travis-ci.org/ControleOnline/assets-essentials.svg)](https://travis-ci.org/ControleOnline/assets-essentials)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ControleOnline/assets-essentials/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ControleOnline/assets-essentials/)
[![Code Coverage](https://scrutinizer-ci.com/g/ControleOnline/assets-essentials/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ControleOnline/assets-essentials/)
[![Build Status](https://scrutinizer-ci.com/g/ControleOnline/assets-essentials/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ControleOnline/assets-essentials/)

More on [Controle Online](http://controleonline.com "Controle Online").

### AUTOMATIC ADD JS/CSS FILES ###
To add your js / css files simply place them following this structure:
```
public/assets/js/application.js
<module>/assets/js/modules/<module>.js
<module>/assets/js/modules/<module>/<controller>.js
<module>/assets/js/modules/<module>/<controller>/<action>.js

public/assets/css/application.css
<module>/assets/css/modules/<module>.css
<module>/assets/css/modules/<module>/<controller>.css
<module>/assets/css/modules/<module>/<controller>/<action>.css
```
If these files exist, they will be added in head:
```
<script type="text/javascript" src="/assets/js/application.js"></script>
<script type="text/javascript" src="/js/modules/<module>.js"></script>
<script type="text/javascript" src="/js/modules/<module>/<controller>.js"></script>
<script type="text/javascript" src="/js/modules/<module>/<controller>/<action>.js"></script>

<link href="/assets/css/application.css" media="screen" rel="stylesheet" type="text/css">
<link href="/css/modules/<module.css" media="screen" rel="stylesheet" type="text/css">
<link href="/css/modules/<module/<controller>.css" media="screen" rel="stylesheet" type="text/css">
<link href="/css/modules/<module/<controller>/<action>.css" media="screen" rel="stylesheet" type="text/css">
```

We also add the libraries that are in bower.json and its dependencies automatically.

If you need to add a library manually (this happens when the library's bower.json is not well configured or if there is more than one js file in each dependency), just add it manually:

```
Assets\Helper\Header::addJsLibs('lazyLoad', '/assets/js/core/LazyLoad.js');
```

Do not forget to keep in the layout file the call to the headers:
```
<html lang="en">
    <head>
        <?= $this->headLink() ?>
        <?= $this->headStyle() ?>
        <?= $this->headScript() ?>
    </head>    
    <body data-js-libs='<?= $this->requireJsLibs ? json_encode($this->requireJsLibs) : '{}' ?>' data-js-files='<?= $this->requireJsFiles ? json_encode($this->requireJsFiles) : '{}' ?>'>
        <div class="show-messages">
            <!-- This div (class .show-messages) receives all system alerts  -->
        </div>
    </body>
</html>   
```