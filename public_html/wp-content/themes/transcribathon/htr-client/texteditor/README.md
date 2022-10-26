/**
 * Copyright (C) READ-COOP SCE - All Rights Reserved
 * 
 * The source code is protected under international copyright law. All rights
 * reserved and protected by the copyright holders.
 * This software is confidential and only available to authorized individuals with the
 * permission of the copyright holders. If you encounter this software and do not have
 * permission, please contact the copyright holders and delete it.
 */


# transkribus-texteditor

This texteditor based on TipTap is used by TranskribusLite and allows to transcribe pages in the PageXML format. In order to display the connection between
text line in the editor and image tht layout must have been recognized for the page. The image needs to be accessible via a IIIF URL.

## Integration

To integrate the editor in any webpage the component can be added as such:

```
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <link rel="icon" href="<%= BASE_URL %>favicon.ico" />
    <title>Transkribus Text-Editor</title>
  </head>
  <body>
    <noscript>
      <strong
        >We're sorry but vue doesn't work properly without JavaScript enabled.
        Please enable it to continue.</strong
      >
    </noscript>
    <div id="transkribusEditor" ref="editor"></div>
  </body>
</html>
```

Without any data attributes a demo IIIF URL and XML is displayed. Pass data attribute data-iiif-url to transmit IIIF info json to the image and the XML via the data-xml-json-string attribute. Furthermore pass a tag-configuration to use customized tags (see tag-config.json for required schema):

```
 <div id=transkribusEditor data-tags='{
                "definitions": {
                  "annotations": [
                    {
                      "name": "Testing",
                      "label": "Testing",
                      "attributes": ["expansion"],
                      "extra": [
                        { "name": "test", "label": "Test attribute", "type": "string" }
                      ],
                      "color": "#ff0000",
                      "icon": 8228
                    }]}}' ref=editor></div>

```

The following example shows how to pass an IIIF-URL and the according XML-JSON as a string (see entire example in full-example.html):

```
<div id="transkribusEditor" data-iiif-url="https://files.transkribus.eu/iiif/2/QJDYEHKUDZIDNBCYBYXBLAZT/info.json" data-xml-json-string='

            {
                "declaration": {
                  "attributes": { "version": "1.0", "encoding": "UTF-8", "standalone": "yes" }
              ....

            ' </div>
```

## Output

The output is set to the data attribute of <div id="transkribusEditor" data-output="...." > and also to the window object as window.output
An event bus instance listens to the 'dataSaved' event and from there the output object with XML and XMLJson can be further porcessed.

```
<script>
  window.eventBus.$on('dataSaved', function (data) {
    console.log("react on saving the data ");
    console.log(data);
  });
</script>
```

## Split up image and editor

If you want to split up text editor and image this would be one way to go:

```
 <!DOCTYPE html>
<html lang=en>

<head>
    <meta charset=utf-8>
    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name=viewport content="width=device-width,initial-scale=1">
    <link rel=icon href=/favicon.ico>
    <title>Transkribus Text-Editor</title>
    <link href=./css/app.c57368ba.css rel=preload as=style>
    <link href=./css/chunk-vendors.b7d36435.css rel=preload as=style>
    <link href=./js/app.2f9ff8fa.js rel=preload as=script>
    <link href=./js/chunk-vendors.03bdc520.js rel=preload as=script>
    <link href=./css/chunk-vendors.b7d36435.css rel=stylesheet>
    <link href=./css/app.c57368ba.css rel=stylesheet>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
</head>

<body><noscript><strong>We're sorry but vue doesn't work properly without JavaScript enabled. Please enable it to
    continue.</strong></noscript>
<div class="columns">
<div class="column">
    <div id=transkribusEditor data-tags='{
        "definitions": {
          "annotations": [
            {
              "name": "Testing",
              "label": "Ok",
              "attributes": ["expansion"],
              "extra": [
                { "name": "expansion", "label": "Was soll das", "type": "string" }
              ],
              "color": "#ff0000",
              "icon": 8228
            }]}}' ref=editor></div>
</div>
<div class="column">
    <div id="tabs">
        <ul>
            <li><a href="#tabs-1">Text Editor</a></li>
            <li><a href="#tabs-2">Configuration</a></li>
            <li><a href="#tabs-3">Metadata</a></li>
        </ul>
        <div id="tabs-1">
            <div id="move"></div>
        </div>
        <div id="tabs-2">
            <h1>Configuration</h1>
            <p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus
                id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus
                eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse
                sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing.
                Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla.
                Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium
                posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et
                purus.</p>
        </div>
        <div id="tabs-3">
            <h1>Metadata</h1>
            <p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel
                vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante.
                Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.
                Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula
                tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
            <p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus.
                Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit.
                Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id
                euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut
                sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula
                velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus.
                Vivamus a libero vitae lectus hendrerit hendrerit.</p>
        </div>
    </div>
</div>
</div>
<script src=./js/chunk-vendors.03bdc520.js></script>
<script src=./js/app.2f9ff8fa.js></script>
<script>
$("#textEditor").appendTo($("#move"));
$(function () {
    $("#tabs").tabs();
});
$("#content").remove();
</script>
<style>
.tile {
    display: contents !important;

}
.hero .is-covering {
    position: fixed;
    left: 0;
    top: 0;
    width: 50%;
    height: 100%;
}
</style>
</body>

</html>
```