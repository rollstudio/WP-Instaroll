![WP-Instaroll](http://rollstudio.it/assets/wp-instaroll/instaroll_github.png)

WP-Instaroll
============

Contributors: FeliceMente (Marco Iannaccone), rformato (Renato Formato), patrick91 (Patrick Guido Arminio)
Tags: instagram, photos, wordpress, plugin
Requires at least: 3.3
Tested up to: 3.3.2

Description
-----------

Simple Instagram plug-in for creating WordPress posts from Instagram photos (both from user stream and by using a specific search tag).

You can browse photos in your user stream, or by the specified tag, import them into WordPress media library and link it to post, with the possibility of choosing between adding it as featured image (post thumbnail) or directly inside the post (default).

The posts created from Instagram photos can be saved as draft or directly published.

The plug-in keeps track of previously published photos and avoids downloading them from Instagram again, if they're already present in local media library, in case a single photo is used for creating multiple posts.

In photos selection panels there's a checkbox for specifying whether to show already published Instagram photos (default) or not.

New in version 1.1.1
----------------------
- better implementation, for the second tab ('Instagram Photos by Tag'), of the option for showing only user photos: before, in case of many never photos, older user photos could not appear, because only most recent photos were returned, while now the user stream is used, so that older photos are not pushed away by newer photos posted by other people

New in version 1.0.4.2
----------------------

- resolved a little issue with Instagram authorization

New in version 1.0.4.1
----------------------

- now the plug-in can be installed directly from WordPress

New in version 1.0.4
--------------------

- possibility of scheduling automatic post creation from Instagram photos (from user stream, tag stream or both)
- possibility of choosing the category to publish Instagram based posts to
- photo selection panel visualization optimized for smaller screens
- minor fixes and improvements

Installation
------------

1. Upload *wp-instaroll* to the */wp-content/plugins/* directory
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to *Settings -> Instaroll Settings* and fill the settings fields
4. Now you're ready to go to **Instaroll Photos** and create posts (there's a panel for user stream photos and a panel for search tag stream)
