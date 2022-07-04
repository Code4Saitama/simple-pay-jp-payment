=== Social Project Donation with PAY.JP ===
Contributors: wpmaster
Donate link: https://base-campus.com/toiro/support/
Tags: donation,social project,payment,shopping,credit card,payment request,e-commerce
Requires at least: 4.9
Tested up to: 7.4
Requires PHP: 5.6
Stable tag: 0.1.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin is a beta version which is expected further improvement.
You can try it out in your environment at your own risk.

== Description ==
This plugin is a modified version of the plugin "Simple PAY.JP Payment".
The source code inherits "Simple PAY.JP Payment"'s features and functions.
This plugin provides payment form by PAY.JP with simple shortcode.
Only for use alongside the plugin "Social Project Donation Management".
If "Simple PAY.JP Payment" plugin is installed in your environment, deactivate it beforehand.

Note:
The supported currency is only JPY so far.

Example of Shortcode:

	[simple-payjp-payment amount=50 form-id="id-string" name='no' result-ok="https://example.tokyo/?page_id=7" result-ng="https://example.tokyo/?page_id=8" ]

 * amount (mandatory*): price in JPY
 * plan-id (mandatory*): subscription plan ID
 * form-id (mandatory): any ID of the form
 * name: show/hide name field ('yes' => show (default), 'no' => hide)
 * result-ok: page url to redirect after payment succeeded if you want to customize success message
 * result-ng: page url to redirect after payment failed if you want to customize failure message
 * prorate: disabled/enabled prorated for subscription payment ('no' => not prorated (default), 'yes' => prorated)

(*) 'amount' is mandatory for single payment. 'plan-id' is mandatory for subscription payment. 'amount' and 'plan-id' should be exclusive.

You can confirm these information of each payments in descripton property of Charge record on PAY.JP admin panel.

Only one shoutcode can be placed in a page.

== API ==

=== Action hook ===

* simplepayjppayment_result_ok: called after payment succeeded
* simplepayjppayment_result_ng: called after payment failed

= Localization =
* English (default) - always included
* Japanese - always included

== Installation ==
1. From the WP admin panel, click “Plugins” -> “Add new”.
2. In the browser input box, type “Social Project Donation with PAY.JP”.
3. Select the “Social Project Donation with PAY.JP” plugin and click “Install”.
4. Activate the plugin.

OR…

1. Unpack the download package.
2. Upload all files to the /wp-content/plugins/ directory.
3. Activate this plugin in \"Plugin\" menu.

== Technical Details ==

How to use is summalized in the following page:
[WordPressプラグイン Simple PAY.JP Payment](https://it-soudan.com/simple-pay-jp-payment/)

== Changelog ==