# Ext: sg_cookie_optin

## Installation

- Install this extension with the Extension Manager, or with composer.
- Add the static TypoScript named "Cookie Optin" to your instance with the "Template" backend module.
- Go into the "Cookie Opt In" backend module, configure it and save it once

## Additional Features

### Open a page without showing the cookie opt in

Just add the parameter "?disableOptIn=1" to you URL, so the necessary JavaScript and Css, which shows the dialog, isn't 
loaded anymore. Here is an example:

``` 
https://www.website-base.dev/impressum/?disableOptIn=1 
```


### Show the cookie opt in, after accepting it

Just add the parameter "?showOptIn=1" to you URL, so the dialog shows up again and the accepted cookies can be modified.
Here is an example:

``` 
https://www.website-base.dev/impressum/?showOptIn=1 
```
