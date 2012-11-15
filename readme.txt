=== Unicode Character Keyboard ===

Contributors: HoosierDragon (Terry O'Brien, alphamale@alphavideoproduction.com)
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=THLBLFT4BV7E2
Tags: post widget, admin widget, HTML special characters, write post, write page, HTML entity codes, Unicode characters
Requires at least: 2.7
Tested up to: 3.4.2
Stable tag: 1.0
Version: 1.0
Author: Terry O'Brien
Author link: http://www.alphavideoproduction.com
License: GPL v2.0

== Description ==

Admin widget on the Write Post or Write Page forms for inserting HTML encodings of Unicode characters into the edit window.

== Installation ==

1. Unzip 'avp-unicode-charkbd.zip' inside the '/wp-content/plugins/' directory or install via the built-in WordPress plugin installer.
2. Activate the plugin through the WordPress 'Plugins' admin page.
3. Go to the "Keyboard -> Settings" admin options page to activate the desired special character sets. All character sets except for the Common set are deactivated by default, and at least one set must be activated for use. 
4. An admin widget entitled "Unicode Characters Keyboard" will now be present in the write post and write page forms. Click on the charater set name to display the characters in the chosen set. 
5. Click on on any character that you wish inserted into your post. 

== Screenshots ==

1. Plugin admin panel for selecting character sets
2. Plugin admin panel for displaying character sets
3. Plugin widget on edit page panel
4. Plugin widget on edit page panel showing the Roman Numeral character set

== Frequently Asked Questions ==

= Why write this plugin? =

I got tired of trying to remember what the keypad codes were for the most common HTML codes available. But when I discovered the wide variety of codes available, being the completeness hack that I am I decided to implement as many of them as possible. See the Unicode Standard page listed below for more information. 

Furthermore, I wanted to learn WordPress plugin design (having done MediaWiki plugins in the past) because I really like the WordPress platform and this seemed like a perfect tool not only to learn all of the various details of plugin design but also give something back to the WordPress community. 

= How do I add character set {X}? =

* First off, make a list of all the character definitions that make up the desired character set. Second, divide them up into named categories and under that, contiguous sub-groups. (See the existing definition files for examples.) The categories are what show up under the sub-headings and the groups are how the rows are divided up in the display. In addition, select which existing type of character sets the new set will fall under, or define a new type for use with this set, and write a comprehensive description of the character set. Optionally, include the location of the Unicode character definition PDF file and the defined character set range information. 
* Next, code the definitions and the additional information into an XML file. The Customize menu sub-page provides information on writing the character set XML definition file, including a breakdown of each XML element and showing the template XML and DTD files and the Common XML definition file. Other XML files can be examined using the plugin editor menu sub-page. Be sure to give the character set and the definition file a unique name, and remember to use only characters, numbers and spaces in the set name.
* Use the Manage File menu sub-page Upload tab to upload the file into the custom module subdirectory, then check the Error Log tab to see what errors or warnings were found in the file: if any errors were found, the file will not be loaded into the module subdirectory. Warnings, on the other hand, are problems in optional sections of the definition file and do not prevent loading. 
* Use the Setting menu sub-page to activate the character set.
* Use the Display menu sub-page to view the character set. 

I would ask that if you create a new character set to send it to me so I can add it to the distribution. 

= All this character set stuff is hard to figure: can you recommend any references? What all is available? =

There are a number of references to the Unicode character codings available. 

* [Unicode Standards](http://www.unicode.org/standard/standard.html "Unicode Standards")
* [Unicode Character Code Charts](http://unicode.org/charts/index.html "Unicode Character Code Charts")
* [HTML 4.01 Character Entity References](http://www.alanwood.net/demos/ent4_frame.html "HTML 4.01 Character Entity References")
* [HTML ISO-8859-1 Reference](http://www.w3schools.com/tags/ref_entities.asp "HTML ISO-8859-1 Reference")
* [HTML - Special Entity Codes](http://tlt.psu.edu/suggestions/international/web/codehtml.html "HTML - Special Entity Codes")
* [Sacred Texts Unicode list](http://www.sacred-texts.com/unicode.htm "Sacred Texts Unicode list")

The References menu sub-page also has a number of recommended online references.

= What are all those symbols on the plugin repository header image? =

* The first line is Conway's Game of Life programmed in the APL language.
* The second line are the astrological symbols for the 12 houses of the Zodiac.
* The third line are the numbers 1 to 4 in Japanese. 

The Symbola font was used to represent the various symbols in the image, which is also the font used to display them in the browser. All of these characters are available through the Unicode Character Keyboard metabox. 

== Changelog ==

= 1.0 =
* Initial public release

= 0.95 =
* Add internationalization functionality

= 0.94 =
* Convert XML processing and error checking to SimpleXML routine
* Add jQuery validation for error checking

= 0.93 =
* Converted menuing tabs to use jQuery functionality

= 0.92 =
* Added comprehensive error reporting on XML file processing

= 0.91 =
* Added message box closure mechanism and message box status icons
* Added postbox handling for text boxes

= 0.9 =
* Final code optimization 
* Last minute code tweaks and presentation changes

= 0.8 =
* Convert admin settings to use own menu page / sub-menu pages
* Add help section and about section

= 0.7 =
* Convert inserted character definition files into XML files for better security (I didn't like the thought of blithely inserting just any and every PHP file in the subdirectories to define the character codes: if a malicious file got put there somehow, it could cause any kind of damage.)

= 0.6 =
* Added reset button and associated handler

= 0.5 =
* Added tabbed character display page

= 0.4 =
* Added tabbed admin page

= 0.3 =
* Added submission status reporting
* Added error status reporting

= 0.2 =
* Minor improvements to reduce processing

= 0.1 =
* Initial coding

== Upgrade Notice ==

= 1.0 =
* Initial Release

== Other Notes ==

= Acknowledgements =

This plugin is based on "HTML Special Characters Helper", an original design by Scott Reilly (aka coffee2code). 

= Languages Sets supported =
* French
* German
* Greek
* Hirigana
* Katakana
* Ogham
* Runic
* Russian
* Spanish
* Tifinagh

= Unicode Sets supported =
* APL
* Alchemical
* Arrows
* Astrology
* Braille
* Computers
* Currency
* Dingbats
* Drawing
* Emoji Pictographs
* Emoticons
* Games
* Latin Extended
* LetterLike
* Mathematics
* Punctuation
* Religious
* Roman Numerals
* Shapes
* Signs
* Small
* Spacing
* Subscripts
* Superscripts
* Symbols
* Technical
