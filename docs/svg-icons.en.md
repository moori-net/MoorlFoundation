---
description: Use the included SVG icons from the Foundation plugin
tags:
  - SVG
  - Icons
  - FontAwesome
---

# Foundation | SVG Icons

Available from Shopware 6.5

Used in:

- [Appflix Classifieds](../AppflixCustomerMarket/index.md)
- [Form Builder](../MoorlForms/index.md)

## Which icons are available?

- Font Awesome 5: [https://fontawesome.com/v5/search](https://fontawesome.com/v5/search) – up to Shopware 6.6
- Font Awesome 6: [https://fontawesome.com/v6/search](https://fontawesome.com/v6/search)
- Font Awesome 7: [https://fontawesome.com/v7/search](https://fontawesome.com/v7/search) – from Shopware 6.7
- Shopware: [https://developer.shopware.com/resources/meteor-icon-kit/](https://developer.shopware.com/resources/meteor-icon-kit/)

## Usage in the admin, e.g. via the settings

In the settings of plugins and CMS elements, there is often an input field called `icon`. The following options are available:

General structure: `<package name>|<icon name>|<size>`

- `solid|search` - Search icon from the package `Shopware Meteor Icon Kit | solid`
- `solid|search|xs` - Search icon from the package `Shopware Meteor Icon Kit | solid` in size `xs`
- `fa7s|map` - Map icon from the package `Font Awesome 7 | solid`
- `fa7b|shopware` - Shopware icon from the package `Font Awesome 7 | brands`
- `fa6b|shopware|xs` - Shopware icon from the package `Font Awesome 6 | brands` in size `xs`

![](images/icon-example.jpg)

In some plugins, it is possible to insert text or an icon. In this case, `icon` is prefixed. Example:

- `icon|solid|search` - Search icon from the package `Shopware Meteor Icon Kit | solid`
- `icon|solid|search|xs` - Search icon from the package `Shopware Meteor Icon Kit | solid` in size `xs`
- `icon|fa7s|map` - Map icon from the package `Font Awesome 7 | solid`
- `icon|fa7b|shopware` - Shopware icon from the package `Font Awesome 7 | brands`
- `icon|fa6b|shopware|xs` - Shopware icon from the package `Font Awesome 6 | brands` in size `xs`

![](images/icon-example-2.jpg)

## Usage in the Twig template

Map icon from the package `Font Awesome 7 | solid` in size `xs`

```twig
{% sw_icon 'map' style { size: 'xs', pack: 'fa7s' } %}
```

Search icon from the package `Shopware Meteor Icon Kit | solid` in size `xs`

```twig
{% sw_icon 'search' style { size: 'xs', pack: 'solid' } %}
```
