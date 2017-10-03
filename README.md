# Drupal 8 Simple Facebook Feed Module
This's my custom module on Drupal 8 to load feed from a Facebook page. This module inspired by [Instagram Block Module] (https://www.drupal.org/project/instagram_block)

# Usage
* First you need to create a Facebook app [Here] (https://developers.facebook.com), get the App ID and App Secret from there.
* Get the User Token from your App trough this [Link] (https://developers.facebook.com/tools/accesstoken/)
* By debug above User Token, you may able to set it to long-lived access token
* Install the module
* Run 'composer require facebook/graph-sdk' to install Facebook graph sdk
* Fill the Facebook Page setting at admin/config/content/facebook_page
