# Depot-Vente

janvier 2024

Le projet depot-vente est programmé en PHP avec le framework "CakePHP".

Coté client (front), le framework VueJS est utilisé pour les dépôts et pour les ventes.



Pour fonctionner le projet doit être installé sur une pile de type LAMP ou WAMP ou XAMP.
Les prorammes suivants doivent être installés sur le serveur (le seveur peut être Windows ou Linux, le pojet peut même être installé sur un Raspberry Pi)
* Apache (v2 au mois)
* PHP8.1 (pas plus recent car sinon probleme de compatibilité)
  PHP8.1-common
  PHP8.1-intl
  PHP8.1-mbstring
  PHP8.1-mysql
  PHP8.1-xml
* mySQL ou mariaDB-server

1 - Créez une base de donnée (sous mySQL ou mariaDB) avec le jeux de caractères uft8. depuis cette base, executez le script SQL creation_db.sql

2 - editer le fichier /etc/PHP/8.1/php.ini
dans la partie [date] définir la timezone sur la même que le serveur (dans mon cas (Europe/Paris)

3 - il faut activer la reecriture d'url pour apache :
si install standard, dans /etc/apache2/apache2.conf il faut modifier le parametre AllowOverride de None à All pour le dossier /var/www/ et faire un lien de rewrite.load du dossier /etc/apache2/mod-available/ dans /etc/apache2/mods-enabled/
puis redemarrer le service apache2

4 - Décompressez l'archive dans le répertoire public du serveur.

5 - Editez le fichier config\app_local.php, retrouvez la séquence de texte suivante (aux allentours de la ligne 40):
```php
'Datasources' => [
    'default' => [
        'host' => 'localhost',
        /*
         * CakePHP will use the default DB port based on the driver selected
         * MySQL on MAMP uses port 8889, MAMP users will want to uncomment
         * the following line and set the port accordingly
         */
        //'port' => 'non_standard_port_number',

        'username' => 'dietpi',
        'password' => 'dietpi',

        'database' => 'depot_vente',
```
Modifiez les paramètres pour correspondre à votre installation

6 - Depuis le répertoire racine du projet, créez 2 répertoires nommés `logs` et `tmp`.  Sous `tmp`, créez les répertoires : `cache`, `session` et `tests`. Sous Unix/Linux, changez les droits d'accès : `chmod -R 777 ./tmp` et `chmod -R 777 ./logs` (lecture écriture pour tous !).

7 - Depuis un navigateur rendez-vous sur la page du projet (par ex http://localhost/depot_vente). Un seul utilisateur est enregistré :
nom de connexion : admin, miot de passe : admin.

8 - Quand vous êtes connecté, tout en bas de page, à gauche, cliquez sur le lien "Admin" qui vous permettra de configurer depot-vente
pour votre évènement.

9 - Les documents PDF générés par _Depot-Vente_ utilisent un fond de page c'est-à-dire une page (de fond) par dessus laquelle sera imprimé le contenu (bon de dépôt, facture...). Ceci vous permet d'apposer entête sur les documents produits par _Depot-Vente_. Le fond de page se trouve sous `vendor/vdw/tcpdf` : `pdf_template.pdf`

-----
