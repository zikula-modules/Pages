[![StyleCI](https://styleci.io/repos/808769/shield)](https://styleci.io/repos/808769)

## Pages 3.x.y

This version requires Zikula Core >= 1.4.1

Pages is a Static Page creation module for the Zikula Application Framework.

### Dev Info

*How to extract translation*
`php app/console translation:extract en --bundle=ZikulaPagesModule --output-format=po`

*How to extract translation with routes*
`php app/console translation:extract en --bundle=ZikulaPagesModule --output-format=po --enable-extractor=jms_i18n_routing`
