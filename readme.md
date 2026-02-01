# JL Ads

A WordPress plugin that ads a custom post type for an Ad.

When creating a new ad:
- Add three versions of the ad
	- Desktop
	- Tablet
	- Mobile
- The form includes:
	- Image: shows the media library so you can upload or pick an existing ad
	- URL to link to
	- Target (self or new window)
- Outside of the 3 versions of the ad, it has a meta box that lets you set the dates for the ad to run, it has three options
	- Always running
	- Start date
	- End date

The way the ad is shown is via a shortcode, like `[jl-ad id=123]`. This shortcode is be shown in a metabox after the ad is published. There is a button next to the shortcode text to copy it to the clipboard.

On the frontend, when the shortcode is called, it first determines if the ad should be shown based off the date. If it should be shown, then it will show all three ads (hidden) but then toggle the visibility of the appropriate ad based off what the current browser width is. It also listens for when the window changes size, and toggles the ad appropriately.