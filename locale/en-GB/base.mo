��    1      �  C   ,      8  	   9  &   C  %   j  �   �  #   6  �   Z  >   �           7  #   K  :   o  $   �  <   �            )     $   F  *   k     �     �     �     �     �  1        E  	   ^  I   h  w   �  z   *	     �	     �	     �	     �	     �	     
     
  "   0
     S
     \
     n
  �   �
  |   +  n   �  �     �   �  g   `  .   �     �  �  
     �       '     �   A     �  p   �  1   l     �     �     �  ;   �       .   2     a     e     s     �     �     �     �     �     �  %        )     G  
   _  C   j  l   �  h        �     �     �     �     �     �     �     	          '     9  �   O  e   �  e   S  {   �  �   5  S   �     I     h        *           1   &                                                                 0             $   "             
   )          	          +   .   /       #         %             '       !   (                      -   ,              Abbrechen Abgeschlossene Bestellungen ausblenden Bei Gastbestellungen und Teilversand. Beschreibung der Zahlung in Mollie. Folgende Variablen stehen zur Verfgung: {orderNumber}, {storeName}, {customer.firstname}, {customer.lastname}, {customer.company} Bestellungen automatisch stornieren Da die JTL-Shop 4 Version dieses Plugins (<code>%s</code>) noch installiert ist, hast du hier die Möglichkeit die Daten zu migrieren. Die ProfilID wird benötigt, um Mollie Components zu verwenden Direkt alles versenden Direkt weiterleiten Erst bei komplett Versand versenden Fallback, falls Locale nicht erkannt oder nicht vorhanden. Füge hier deinen Mollie API Key ein Hier kann das Bestellabschluss-Verhalten eingestellt werden. Ja Ja, Checkbox Kunden bei Mollie anlegen (Customer API)) Migration kann durchgeführt werden. Migration kann nicht durchgeführt werden: Nach 3 Sekunden weiterleiten Nein Nein, ich mache alles manuell. Nicht automatisch weiterleiten Nur Kunden & Komplettversand Nur bezahlte Bestellungen in die WAWI übertragen Payment API Beschreibung Profil ID Soll Versandinformation für OrderAPI Methoden an Mollie gesendet werden? Soll bei fehlgeschlagener Zahlung die Bestellung storniert werden? Angabe in Stunden nach Bestellung. (0 = deaktiviert) Soll bei fehlgeschlagener Zahlung ein Zahlungslink verschickt werden? Angabe in Stunden nach Bestellung. (0 = deaktiviert) Synctype.data Synctype.paymentSettings Synctype.settings Synctype.syncShipping Synctype.uninstall Teilversand Verhalten Test API als Admin Unbezahlte Bestellungen stornieren Upgrade! Upgrade-Assistent Versand bei Mollie Wenn diese Einstellung aktiviert ist, hat der Kunde die Möglichkeit, per Checkbox, seine Kundendaten bei Mollie zu speichern. Z.B. für Single-Click Checkout benötigt. Wenn diese Einstellung aktiviert ist, werden komplett Stornierte Bestellungen auch bei Mollie storniert oder rückerstattet. Wenn diese Einstellung aktiviert ist, werden versendete, bezahlte Bestellungen nicht in der Tabelle angezeigt. Wenn diese Einstellung aktiviert ist, wird im Shop automatisch die TestAPI verwendet, wenn man als Admin im Backend eingeloggt ist. Wenn diese Einstellung aktiviert ist, wird versucht, die im Bestellvorgang ausgewählte Mollie-Zahlungsart zurückzusetzen, somit kann der Kunde zwischen allen aktiven Mollie-Zahlarten zu wählen. Wenn diese Einstellung deaktiviert ist, können alle Bestellungen direkt von der WAWI abgerufen werden. Zahlart bei erneutem Zahlvorgang zurücksetzen Zahlungserinnerung Project-Id-Version: ws5_mollie
PO-Revision-Date: 2021-08-12 11:05+0200
Last-Translator: 
Language-Team: 
Language: en_GB
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=(n != 1);
X-Generator: Poedit 3.0.1
X-Poedit-Basepath: ../..
X-Poedit-SourceCharset: UTF-8
X-Poedit-KeywordsList: __
X-Poedit-SearchPath-0: vendor/webstollen/jtl5-plugin/src/tpl/admin
X-Poedit-SearchPath-1: adminmenu
X-Poedit-SearchPathExcluded-0: *.js
 Cancel Hide completed Orders For guest orders and partial shipments. Description of the payment in Mollie. The following variables are available: {orderNumber}, {storeName}, {customer.firstname}, {customer.lastname}, {customer.company} Auto-Cancel orders Since the JTL-Shop 4 version of this plugin (<code>%s</code>) is still installed, you can migrate the data here. The Profile ID is needed to use Mollie Components Ship directly Redirect directly Only ship, when complete Fallback if the locale is not recognized or does not exist. Enter your Mollie API Key here The order completion behavior can be set here. Yes Yes, checkbox Use Customer API Migration can be done. Migration can't be done: Redirect after %d seconds No No, I do everything myself. No automatic redirect Only Customers and complete Shipments Only sync paid orders to WAWI Payment API description Profile ID Should shipping information for OrderAPI methods be sent to Mollie? Should the order be canceled if the payment has failed? Specified in hours after ordering. (0 = deactivated) Should a payment link be sent if the payment fails? Specified in hours after ordering. (0 = deactivated) Plugin data Payment method settings Plugin settings Shipping method connections Uninstall Plugin Partial Shipments Test API as Admin Cancel unpaid orders Upgrade! Upgrade assistant Shipments with Mollie If this setting is activated, the customer has the option of using a checkbox to save his customer data with Mollie. E.g. required for single-click checkout. If this setting is activated, completely canceled orders will also be canceled or refunded at Mollie. If this setting is activated, orders that have been sent and paid for are not displayed in the table. If this setting is activated, the TestAPI is automatically used in the shop if you are logged into the backend as an admin. If this setting is activated, an attempt is made to reset the Mollie payment method selected in the order process, so the customer is able to choose between all active Mollie payment methods. If this setting is deactivated, all orders can be called up directly from the WAWI. Reset Payment method on re-pay Payment reminder 