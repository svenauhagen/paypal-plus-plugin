=== PayPal Plus for WooCommerce ===
Contributors: inpsyde, biont
Tags: paypal, paypal plus, woocommerce, payment, zahlungsarten, rechnung, lastschrift, kreditkarte
Requires at least: 4.4
Tested up to: 4.7.4
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 
The official WordPress Plugin for WooCommerce - adds payment via PaypPal, Direct debit, Credit card and Pay upon invoice to your WooCommerce Shop.
 
== Description ==

== Installation ==

= Minimum Requirements = 

* WooCommerce >= 3.0

Weitere Mindestanforderungen richten sich nach den Voraussetzungen für WooCommerce:

* PHP 5.6 or greater
* MySQL 5.6 or greater
* WordPress 4.4+
* WP Memory limit of 64 MB or greater (128 MB or higher is preferred)

= Automatic Installation = 

Dies ist der einfachste Weg, das PayPal Plus Plugin zu installieren. 
1. Logge dich in deine WordPress Installation ein.
2. Gehe zum Menüpunkt *Plugins* und dort auf *Installieren*.
3. Suche nach *PayPal Plus for WooCommerce*. Solltest du mehrere Plugins aufgelistet bekommen, achte darauf, dass der Pluginautor *Inpsyde* ist.
4. Klicke auf *Jetzt installieren* und warte bis WordPress die erfolgreiche Installation meldet. 
5. Aktiviere anschließend das Plugin. Die Einstellungen findest du unter *WooCommerce => Einstellungen => Kasse => PayPal Plus*.

**Achtung:** du benötigst WooCommerce 3.0 oder höher, um PayPal Plus nutzen zu können. Andernfalls ist die Settingsseite des Plugins nicht vorhanden. Eine Meldung in deinem WordPress Backend wird dich darauf hinweisen, sollte nicht die richtige WooCommerce Version aktiviert sein.
 
= Manual Installation = 

Falls die automatische Installation bei dir nicht funktioniert, lade das Plugin hier über den *Herunterladen*-Button herunter. Entpacke das Archiv und lade den Ordner per FTP in das Verzeichnis `wp-content\plugins` deiner WordPress Installation hoch. Gehe zu *Plugins => Installierte Plugins* und klicke bei *PayPal Plus für WooCommerce* auf *Aktivieren*.

= Einrichtung des Plugins = 

== Frequently Asked Questions ==
 
= Ich habe WooCommerce in einer kleineren Version installiert. Kann ich PayPal Plus für WooCommerce dennoch nutzen? =
 
Nein, das Plugin ist nur mit WooCommerce Versionen >= 3.0 kompatibel. Wir raten dir dringend ein Update durchzuführen. Denke aber daran, vorher ein Backup deiner Installation anzulegen. 

= Was muss ich beachten, wenn ich ein PayPal Konto für mehrere Shops verwende? =

Du musst zwingend in den Einstellungen von *PayPal Plus für WooCommerce* pro Shop einen eindeutigen Rechnungspräfix vergeben. Andernfalls wird PayPal keine Bestellungen mit identischer Rechnungsnummer akzeptieren.

= Für welche Länder kann ich PayPal Plus für WooCommerce nutzen? = 

Du kannst das Plugin derzeit für Deutschland einsetzen. 

= Welche Zahlungsarten kann ich mit PayPal Plus für WooCommerce in meinem Shop einbinden? = 

Du kannst mit unserem Plugin alle von PayPal Plus angebotenen Zahlungsarten einrichten: beahlen mit deinem PayPal-Konto, per Lastschrift, per Kreditkarte und bezahlen per Rechnung.
 
== Screenshots ==
 
1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot
 
== Changelog ==
 
= 1.0 =

Initial Release
 
== Upgrade Notice ==
 
= 1.0 =

Dies ist die erste Version. Aktualisiere dein System, wenn Updates verfügbar sind um sicherzustellen, dass das Plugin einwandfrei funktioniert.
