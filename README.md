# Contao Fallback Articles

This Contao extension provides a basis for developers to implement methods that can get fallback articles when a page has no articles for a layout section.

## Implementations

* `qbus/contao-inherit-fallback-articles`: Inherit articles from the next section up the page tree that does contain articles.
* `qbus/contao-home-fallback-articles`: Get articles from the appropriate layout section on the page tree's home page.

## Usage

Fallback Articles provides the hook `getFallbackArticles`, language labels for implementations, and integration options in `tl_layout`.

### Hook `getFallbackArticles`

The hook is called whenever a layout section does not contain any articles *and* a fallback method for that section is selected in the layout settings. The hook provides the page id and the name of the section. As a return value, it expects the fallback content as a string or `false` if no content could be provided by the fallback method.

Your hook listener must be named in order for it to be usable in the layout settings.

#### Example

Register the hook:

```
// config/config.php
$GLOBALS['TL_HOOKS']['getFallbackArticles']['example_fallback_name'] = [
    'ExampleHookClass',
    'onGetFallbackArticles'
];
```

Implement your fallback method:
```
class ExampleHookClass
{
    public function onGetFallbackArticles($pageId, $section)
    {
        return $this->exampleFallbackMethod($pageId, $section) ?? false;
    }

    protected function exampleFallbackMethod($pageId, $section): ?string
    {
        // do something, return string or null
    }
}
```

### Language labels for the fallback method

Use the name of your hook implementation as a key to provide a short description of your fallback method under the language key `fallback_articles_methods`.

#### Example

```
// languages/en/fallback_articles_methods.php
$GLOBALS['TL_LANG']['fallback_articles_methods']['example_fallback_name'] = 'Example description';
```
or
```
<!-- languages/en/fallback_articles_methods.xlf -->
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.1">
  <file>
    <body>
      <trans-unit id="fallback_articles_methods.example_fallback_name">
        <target>Example description</target>
      </trans-unit>
    </body>
  </file>
</xliff>
```

### Backend integration

Open the layout settings and select the checkbox "Define fallback articles". In the tabular field "Fallback articles", select the methods to be used per layout section. The methods are evaluated from bottom to top. The first one (from the bottom) to return any content "wins", i. e. the methods following it are not used. In other words, the fallback content in a section does not accumulate when multiple methods are defined.

## TODO

Drop Contao 3.5 support and use the `getArticles` hook to get rid of the current replication of the core's module assembly.
