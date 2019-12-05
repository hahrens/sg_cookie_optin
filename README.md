# Ext: sg_cookie_optin

## Installation

1. Install this extension with the Extension Manager, or with composer.

2. Add the static TypoScript named "Cookie Optin" to your instance with the "Template" backend module.
   
    - Open up the "Template" module in the backend of TYPO3.
    - Go to your root site page within the page tree.
    - Choose "Info/Modify" at the select on the top.
    - Click on the button "Edit the whole template record".
    - Select the tab "Includes".
    - Choose the template "Cookie Optin (sg_cookie_optin)" on the multi select box with the name "Include static (from extensions)"
    - Save

3. Go into the "Cookie Opt In" backend module, configure it and save it once.

## How to add scripts / How to rewrite the script HTML?

Unfortunately we can't support HTML code for the cookie scripts, because of security cases. So you need to rewrite the
HTML code to javascript. Here's an example for the Google Tag Manager:

HTML:

```html
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

JavaScript:

```javascript
var script = document.createElement('script');
script.setAttribute('type', 'text/javascript');
script.setAttribute('async', true);
script.setAttribute('src', 'https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID');
document.body.appendChild(script);

window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'GA_MEASUREMENT_ID');
```

## How is the structure of our cookie?

In order for us to know which cookie groups the user has accepted, we must also store an essential cookie. 
The structure is as follows:

**Name**: cookie_optin
**Example data**: essential:1|analytics:0|performance:1
**Explanation**: The user has accepted the essential and performance groups, but not the analytics one.

## Additional Features

### Open a page without showing the cookie opt in

Just add the parameter "?disableOptIn=1" to your URL, so the necessary JavaScript and Css, which shows the dialog, isn't 
loaded anymore. Here is an example:

``` 
https://www.sgalinski.de/?disableOptIn=1 
```

### Show the cookie opt in, after accepting it

Just add the parameter "?showOptIn=1" to your URL, so the dialog shows up again and the accepted cookies can be modified.
Here is an example:

``` 
https://www.sgalinski.de/?showOptIn=1 
```

### IFrames

#### Add an additional description for a iframe on the opt in

Just add the data attribute "data-consent-description" to an iframe HTML tag, like in the example below:

```html
<iframe width="560" height="315" src="https://www.youtube-nocookie.com/XYZ" data-consent-description="An additional description about this video!"></iframe>
```

#### Whitelist an iframe for the external content opt in logic

Just add the data attribute "data-iframe-allow-always" to an iframe HTML tag, like in the example below:

```html
<iframe width="560" height="315" src="https://www.youtube-nocookie.com/XYZ" data-iframe-allow-always="1"></iframe>
```
