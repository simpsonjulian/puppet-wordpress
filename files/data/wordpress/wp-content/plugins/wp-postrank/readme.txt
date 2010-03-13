=== PostRank ===
Contributors: zedlander, singpolyma, igrigorik
Tags: rss, feeds, postrank, statistics, stats, widget, analytics
Requires at least: 2.7
Tested up to: 2.9
Stable tag: 1.2

Showcase your Top Posts with the PostRank widget, track social media analytics, and engage with your readers from the WP dashboard.

== Description ==

This plugin adds the PostRank [Top Posts Widget & Analytics](http://www.postrank.com/publishers/wordpress) to your blog. PostRank measures the audience [engagement with each story](http://www.postrank.com/postrank#what) by analyzing the types and frequency of online social media interactions -- comments, tweets, diggs, etc. The more interesting or relevant the story is, the more active your readers will be in organizing, responding to, and sharing it.

The Top Posts Widget enables readers to easily see the blog's best content, with the highest overall [PostRank scores](http://www.postrank.com/postrank/). Readers can also search for the best content by specific topics of interest. The Analytics Dashboard integrates specific social social hub analytics metrics in-line with each post, or an overview of the blog's engagement for the last 30 days.

By measuring the engagement with each story the PostRank Top Posts Widget features your best new and archived content, providing another channel to gain readership, increase page views and clicks, RSS subscriptions and ad revenues.

> The PostRank Widget on Ruby Inside is really helping get viewers to surf around more. A big page views per visit increase. — Peter Cooper ([www.rubyinside.com](http://www.rubyinside.com))

In adddition to the Top Posts Widget, the PostRank plugin integrates social media analytics features directly into your admin interface. The Manage Posts page will display each post's current PostRank score, and hovering over it will reveal your current engagement metrics from around the web: delicious/magnolia bookmarks, twitter/jaiku messages, diggs, and [many others](http://www.postrank.com/postrank#how). 

See the [Screenshots](screenshots/) for examples of Top Posts Widget customizations and social media analytics previews.

== Installation ==

1. Unzip `wp-postrank.zip`, and upload its contents into the `/wp-content/plugins/` directory
2. Activate `PostRank` through the 'Plugins' menu in WordPress
3. Go to `Appearance > Widgets` to add the widget to your sidebar

= No Widget Support? =
If you don't have a widget-enabled theme, you can add the Top Posts Widget to your sidebar with this code:

`<?php if(function_exists('the_postrank_widget')) the_postrank_widget(); ?>`

You can also specify the theme name, and number of posts to show:

`the_postrank_widget('hawt',10);`

The valid themes are:

* blueSteel
* hawt
* springMeadows
* theDarkSide
* hotChocolate
* siren
* pimento
* diner
* diy

You can see them in action on our [customize page](http://www.postrank.com/publishers/customize).

== Frequently Asked Questions ==

= What is PostRank, how does it work? =
Great question, take a look at our [indepth description on postrank.com](http://www.postrank.com/postrank).

= What are Social Media Analytics? = 
PostRank scoring is based on analysis of the "5 Cs" of engagement: [creating, critiquing, chatting, collecting, and clicking](http://www.postrank.com/postrank#how). By collecting interaction metrics from around the internet our system calculates the overall engagement score of each story and a PostRank value is determined.

= What kind of analytics do I get? =
The plugin integrates PostRank social media analytics features directly into your admin interface. The Manage Posts page will display each post's current PostRank score, and hovering over it will reveal your current engagement metrics from around the web: delicious/magnolia bookmarks, twitter/jaiku messages, diggs, and [many others](http://www.postrank.com/postrank#how). See the [Screenshots](../screenshots/) for examples.

== Template Tags ==
This plugin offers several custom theme template tags, so you can integrate PostRanks into your WordPress theme.  They can all be called from within [The Loop](http://codex.wordpress.org/The_Loop), or you can pass them a specific post id.
There are currently three template tags:
= the_postrank_badge(post_id) =
This template tag displays a nicely formatted PostRank badge.

Example: `<?php the_postrank_badge(); ?>`
= the_postrank(post_id) =
This template tag displays the numerical PostRank of a post.  Since it only displays a number, you can format it to your liking.

Example: `<?php the_postrank(); ?>`
= the_postrank_color(post_id) =
This template tag displays the CSS color code for the PostRank of a post.  It displays a string like 'ffe08e'.

Example: `<div style="background-color:#<?php the_postrank_color(); ?>;">`
== Screenshots ==
1. Top Posts
2. Search
3. Dashboard Analytics
4. Widget Settings
5. PostRanks and Social Media Analytics in the Admin Interface
6. General PostRank Settings
