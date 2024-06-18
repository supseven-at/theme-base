# supseven/theme-base

**DOCUMENTATION AND README WORK IN PROGRESS**

---

# How to use this package

- require `supseven/theme-base` in your local sitepackage extension
  `supseven/theme_project`
    - some of the configuration is loaded automatically
    - most of the configuration needs to be used/imported in your project's
      sitepackage
    - The package `supseven/theme-base` can be called "Sitepackage library"
- the local package `theme_project` (`supseven/theme-project`) is used like the theme ext before.
    - All Frontend related stuff is located in `supseven/theme-project`
    - The name keeps the same for all of our projects
- The final sitepackage extension is named after the TYPO3 site:
  e.g. `theme_site_sitename`
    - it requires the local package `supseven/theme_project`
    - this package is set as sitepackage within the TYPO3 site configuration for
      `b13/bolt`.

By using this construct every TYPO3 project can easily grow with multiple sites.

---

# Needed site settings

_settings.yaml:_

```yaml
themeBase:

    typo3:
        linkvalidator:
            fromemail: '%env(MAIL_SENDEREMAIL_DEFAULT)%'
            externalhttpAgentEmail: '%env(MAIL_SENDEREMAIL_DEFAULT)%'
            externalhttpAgentName: '%env(TYPO3_LINKVALIDATOR_EXTERNAL_AGENT_NAME)%'

        tcemain:
            permissions:
                groupid: 1

    page:
        fluidtemplate:
        rootPaths:
            # Used for rootPaths index 20
            sitepackage: theme_project
            # Used for rootPaths index 30. Must not be used if not necessary
            sitepackageSiteOverride: theme_site_sitename
```

---

# Changes added while creating the extraction of ext:theme in TYPO3 12

## Change: conditions for applicationContext

Conditions checking the applicationContext must check on "beginning with" as
there are szenarios in future, where multiple environments (hor. scaling) is
used. Checking for an exact applicationContext is then not the best way
anymore. Using `[applicationContext matches "#^Production/Live#"]` is
recommended from now on.

---

## Breaking Change: Variable RootUid removed

The FLUIDTEMPLATE variable `RootUid` was removed. The successor is
[TYPO3 SiteProcessor](https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/9.5.x/Feature-87748-AddSiteProcessor.html?highlight=siteprocessor)
and can be used with `{site.rootPageId}`

---

## Breaking Change: FLUIDTEMPLATE option extbase.controllerExtensionName removed

We used this option for years to have this magic config where the following
Fluid ViewHelpers does not need an extension name to work within fluid page:

- `f:translate`
- `f:uri.resource`

This option is removed as the TYPO3 core team has planned to remove it in the
upcoming major TYPO3 version 13/14.

Beside that, it's better using following approaches (how some may have already
done  it.):

- A) `{f:translate(key: 'LLL:EXT:my_extension/Resources/Private/Language/yourFile.xlf:yourKey')}`
- or B) if the extension uses the TYPO3 specific locallang conventions:
  `{f:translate(key:'yourKey', extensionName='MyOtherExtension')}`

---

## Change: Common restrictions were moved to a dedicated package

Configs like "eval trim" and "eval required" for `tt_content.header` are now
encapsulated within `josefglatz/bureaucratic`.
[Take a look](https://github.com/josefglatz/bureaucratic)

---

## Change: typo3/cms-adminpanel User TsConfig configuration is loaded automatically

The User TsConfig part is automatically loaded and therefore activate. If  there
are needs to disable or reconfigure the adminpanel User TsConfig just overwrite
the configuration in `supseven/theme_project`.

> The TypoScript Setup `config.admPanel = 1` is loaded when the TypoScript setup
> is included within your sitepackage base extension `supsven/theme-project`!

---

## Change: Our defacto common Page TsConfig is loaded automatically

The TYPO3 Linkvalidator loads the Page TsConfig globally, since TYPO3 12. Reason
enough to make the same step for our beloved "theme":

- `typo3/cms-linkvalidator` configuration is added automatically. New site
  settings where added for configuring them per TYPO3 site.
- TCEFORM: Some common `TCEFORM.pages` configuration is activated automatically.
- TCEMAIN: Common ClearCache and CopyBehaviours are loaded automatically. Also
  the default permissions are set and can be configured TYPO3 site specific.
    - See `EXT:theme_base/Configuration/SiteConfiguration/Settings/DefaultSettings.yaml`
    for default settings which can be added to TYPO3 site configuration via yaml
    imports.

---

