Sat 1/10/2026 12:42 AM



[WordPress Plugin Directory] Review in Progress: Arabic Search Enhancement
WordPress
.org Plugin Directory<plugins@
wordpress
.org>
​You​
🙌 Thank you for the changes "yasircs4"!

Your plugin is not yet ready to be approved, you are receiving this email because the volunteers have manually checked it and have found some issues in the code / functionality of your plugin.

Please check this email thoroughly, address any issues listed, test your changes, and upload a corrected version of your code if all is well.

List of issues found


## PHP Syntax errors

We ask developers to pay attention to their PHP syntax. This kind of errors can come wide variety of issues, from obvious ones (a missing semicolon, mismatched braces, or incorrect use of PHP keywords) to other cases where a combination of functions just doesn't make sense.

Please take a look at what we found in this code:
src/Core/MultiLanguageNormalizer.php:16 namespace ArabicSearchEnhancement\Core;
   # ↳ PHP syntax error: Namespace declaration statement has to be the very first statement or after any declare call in the script
src/Core/AdvancedSearchFeatures.php:16 namespace ArabicSearchEnhancement\Core;
   # ↳ PHP syntax error: Namespace declaration statement has to be the very first statement or after any declare call in the script
src/Core/PerformanceOptimizer.php:15 namespace ArabicSearchEnhancement\Core;
   # ↳ PHP syntax error: Namespace declaration statement has to be the very first statement or after any declare call in the script
src/Utils/RepositorySubmissionHelper.php:16 namespace ArabicSearchEnhancement\Utils;
   # ↳ PHP syntax error: Namespace declaration statement has to be the very first statement or after any declare call in the script



## Calling files remotely

Offloading images, js, css, and other scripts to your servers or any remote service (like Google, MaxCDN, jQuery.com etc) is disallowed. When you call remote data you introduce an unnecessary dependency on another site. If the file you're calling isn't a part of WordPress Core, then you should include it -locally- in your plugin, not remotely. If the file IS included in WordPress core, please call that instead.

An exception to this rule is if your plugin is performing a service. We will permit this on a case by case basis. Since this can be confusing we have some examples of what are not permitted:
Offloading jquery CSS files to Google - You should include the CSS in your plugin.
Inserting an iframe with a help doc - A link, or including the docs in your plugin is preferred.
Calling images from your own domain - They should be included in your plugin.
Here are some examples of what we would permit:
Calling font families from Google or their approved CDN (if GPL compatible)
API calls back to your server to process possible spam comments (like Akismet)
Offloading comments to your own servers (like Disqus)
oEmbed calls to a service provider (like Twitter or YouTube)
Please remove external dependencies from your plugin and, if possible, include all files within the plugin (that is not called remotely). If instead you feel you are providing a service, please re-write your readme.txt in a manner that explains the service, the servers being called, and if any account is needed to connect.

Example(s) from your plugin:
docs/index.html:72 <meta property="og:image" content="https://yasircs4.github.io/wp-plg-arabic-search-enhancement/assets/og-image.png">
docs/index.html:81 <meta property="twitter:image" content="https://yasircs4.github.io/wp-plg-arabic-search-enhancement/assets/og-image.png">
docs/index.html:63 "screenshot": "https://yasircs4.github.io/wp-plg-arabic-search-enhancement/assets/screenshots/admin-settings.png"



## Undocumented use of a 3rd Party / external service

Plugins are permitted to require the use of third party/external services as long as they are clearly documented.

When your plugin reach out to external services, you must disclose it. This is true even if you are the one providing that service.

You are required to document it in a clear and plain language, so users are aware of: what data is sent, why, where and under which conditions.

To do this, you must update your readme file to clearly explain that your plugin relies on third party/external services, and include at least the following information for each third party/external service that this plugin uses:
What the service is and what it is used for.
What data is sent and when.
Provide links to the service's terms of service and privacy policy.
Remember, this is for your own legal protection. Use of services must be upfront and well documented. This allows users to ensure that any legal issues with data transmissions are covered.

Example:
== External services ==

This plugin connects to an API to obtain weather information, it's needed to show the weather information and forecasts in the included widget.

It sends the user's location every time the widget is loaded (If the location isn't available and/or the user hasn't given their consent, it displays a configurable default location).
This service is provided by "PRT Weather INC": terms of use, privacy policy.


Example(s) from your plugin:
# Domain(s) not mentioned in the readme file.
docs/index.html:69 <meta property="og:url" content="https://yasircs4.github.io/wp-plg-arabic-search-enhancement/">
docs/index.html:78 <meta property="twitter:url" content="https://yasircs4.github.io/wp-plg-arabic-search-enhancement/">



## Generic function/class/define/namespace/option names

All plugins must have unique function names, namespaces, defines, class and option names. This prevents your plugin from conflicting with other plugins or themes. We need you to update your plugin to use more unique and distinct names.

A good way to do this is with a prefix. For example, if your plugin is called "Arabic Search Enhancement" then you could use names like these:
function arabseen_save_post(){ ... }
class ARABSEEN_Admin { ... }
update_option( 'arabseen_options', $options );
add_shortcode( 'arabseen_shortcode', $callback );
register_setting( 'arabseen_settings', 'arabseen_user_id', ... );
define( 'ARABSEEN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
global $arabseen_options;
add_action('wp_ajax_arabseen_save_data', ... );
namespace yasircs4\arabicsearchenhancement;

Disclaimer: These are just examples that may have been self-generated from your plugin name, we trust you can find better options. If you have a good alternative, please use it instead, this is just an example.

The prefix should be at least four (4) characters long (don't try to use two- or three-letter prefixes anymore). We host almost 100,000 plugins on WordPress.org alone. There are tens of thousands more outside our servers. Believe us, you're likely to encounter conflicts.

You also need to avoid the use of __ (double underscores), wp_ , or _ (single underscore) as a prefix. Those are reserved for WordPress itself. You can use them inside your classes, but not as stand-alone function.

Please remember, if you're using _n() or __() for translation, that's fine. We're only talking about functions you've created for your plugin, not the core functions from WordPress. In fact, those core features are why you need to not use those prefixes in your own plugin! You don't want to break WordPress for your users.

Related to this, using if (!function_exists('NAME')) { around all your functions and classes sounds like a great idea until you realize the fatal flaw. If something else has a function with the same name and their code loads first, your plugin will break. Using if-exists should be reserved for shared libraries only.

Remember: Good prefix names are unique and distinct to your plugin. This will help you and the next person in debugging, as well as prevent conflicts.

Analysis result:
# This plugin is using the prefix "arabic_search" for 7 element(s).
# This plugin is using the prefix "arabicsearchenhancement" for 5 element(s).

# Looks like there are elements not using common prefixes.
src/Core/Plugin.php:294 update_option($status_option, Configuration::VERSION);
# ↳ Detected name: ase_tables_version
src/Admin/SettingsPage.php:565 wp_localize_script('arabic-search-enhancement-admin', 'arabicSearchAdmin', ['nonce' => wp_create_nonce('arabic_search_admin'), 'i18n' => ['testRunning' => esc_html__('Running Tests...', 'arabic-search-enhancement'), 'runTest' => esc_html__('Run Self-Test', 'arabic-search-enhancement'), 'clientTestsComplete' => esc_html__('Client-side tests completed.', 'arabic-search-enhancement'), 'jqueryAvailable' => esc_html__('jQuery Available', 'arabic-search-enhancement'), 'jqueryLoaded' => esc_html__('jQuery is loaded', 'arabic-search-enhancement'), 'jqueryMissing' => esc_html__('jQuery not detected', 'arabic-search-enhancement'), 'jqueryError' => esc_html__('jQuery test failed', 'arabic-search-enhancement'), 'arabicRendering' => esc_html__('Arabic Text Rendering', 'arabic-search-enhancement'), 'arabicRenderingPass' => esc_html__('Arabic text renders correctly', 'arabic-search-enhancement'), 'arabicRenderingWarn' => esc_html__('Arabic text rendering may have issues', 'arabic-search-enhancement'), 'arabicRenderingError' => esc_html__('Arabic rendering test failed', 'arabic-search-enhancement'), 'testNote' => esc_html__('Note: Server-side tests require the plugin to be fully activated and functional.', 'arabic-search-enhancement')]]);
# ↳ Detected name: arabicSearchAdmin
src/Admin/SearchAnalyticsDashboard.php:103 wp_localize_script('arabic-search-analytics', 'arabicSearchAnalytics', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('arabic_search_analytics_nonce'), 'strings' => ['loading' => esc_html__('Loading analytics...', 'arabic-search-enhancement'), 'error' => esc_html__('Error loading data', 'arabic-search-enhancement'), 'no_data' => esc_html__('No data available', 'arabic-search-enhancement')]]);
# ↳ Detected name: arabicSearchAnalytics


Note: Options and Transients must be prefixed.

This is really important because the options are stored in a shared location and under the name you have set. If two plugins use the same name for options, they will find an interesting conflict when trying to read information introduced by the other plugin.

Also, once your plugin has active users, changing the name of an option is going to be really tricky, so let's make it robust from the very beginning.

Example(s) from your plugin:
src/Core/Plugin.php:294 update_option($status_option, Configuration::VERSION);



👉 Continue with the review process.

Read this email thoroughly.

Please, take the time to fully understand the issues we've raised. Review the examples provided, read the relevant documentation, and research as needed. Our goal is for you to gain a clear understanding of the problems so you can address them effectively and avoid similar issues when maintaining your plugin in the future.
Note that there may be false positives - we are humans and make mistakes, we apologize if there is anything we have gotten wrong. If you have doubts you can ask us for clarification, when asking us please be clear, concise, direct and include an example.

📋 Complete your checklist.

✔️ I fixed all the issues in my plugin based on the feedback I received and my own review, as I know that the Plugins Team may not share all cases of the same issue. I am familiar with tools such as Plugin Check, PHPCS + WPCS, and similar utilities to help me identify problems in my code.
✔️ I tested my updated plugin on a clean WordPress installation with WP_DEBUG set to true.
⚠️ Do not skip this step. Testing is essential to make sure your fixes actually work and that you haven’t introduced new issues.

✔️ I acknowledge that this review will be rejected if I overlook the issues or fail to test my code.
✔️ I went to "Add your plugin" and uploaded the updated version. I can continue updating the code there throughout the review process — the team will always check the latest version.
✔️ I replied to this email. I was concise and shared any clarifications or important context that the team needed to know.
I didn't list all the changes, as the team will review the entire plugin again and that is not necessary at all.

ℹ️ To make this process as quick as possible and to avoid burden on the volunteers devoting their time to review this plugin's code, we ask you to thoroughly check all shared issues and fix them before sending the code back to us. I know we already asked you to do so, and it is because we are really trying to make it very clear.

While we try to make our reviews as exhaustive as possible we, like you, are humans and may have missed things. We appreciate your patience and understanding.

Review ID: R arabic-search-enhancement/yasircs4/21Oct25/T6 9Jan26/3.8RC1


--
WordPress Plugins Team | plugins@wordpress.org
https://make.wordpress.org/plugins/
https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
https://wordpress.org/plugins/plugin-check/
{#HS:3115545755-888698#} 