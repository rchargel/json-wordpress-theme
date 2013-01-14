JSON Wordpress Theme
====================

This is a JSON theme for wordpress.  It essentially turns your wordpress server into a REST API. It cannot be shared as a regular wordpress theme as it contains no HTML in its output.

Installation
-----------

This really could not be any simpler.  Download the source:

    git clone git@github.com:rchargel/json-wordpress-theme.git json-wordpress-theme

This will create a directory in the current folder called 'json-wordpress-theme'.  Enter that directory, and you should see this README file, 
and another directory, with that same name: 'json-wordpress-theme'.  Just zip up that directory, and you have the theme ready to install.

    zip json-wordpress-theme.zip json-wordpress-theme/*

Now just log into wordpress and install it like any other theme.

Known Issues
------------

At this time, I have only deployed this theme in my own website.  It is not really ready for production release.  I know of at least two bugs.

1. The RSS feed will not work with these URLs.
2. For some reason, only the default permalink format will work when fetching data: eg: /wordpress/?p=post_id

Also, you will not be able to preview your pages, as all you will see is the JSON that is created.

Thanks and have fun with it.
Rafael
