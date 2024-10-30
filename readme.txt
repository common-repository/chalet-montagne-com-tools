=== Chalet-Montagne.com Tools ===
Tags: chaletmontagne, synchronisation, cron, planning, tarifs
Requires at least: 4.5
Tested up to: 6.0
Stable tag: 6.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Synchronisez votre site avec votre compte Chalet Montagne pour afficher le planning de disponibilités et les tarifs ainsi qu'un formulaire de contact.

== Description ==

Ce plugin vous permet de synchroniser votre site avec votre compte Chalet Montagne.
Une tâche de fond récupère les informations disponibles sur le site Chalet Montagne et les met à jour automatiquement sur vote site.
Vous pourrez afficher un calendrier et/ou une liste de tarifs sur une page ou un article avec les informations à jour.
Pour cela, il suffira de copier/coller une partie de code (appelé 'shortcode') dans une page pour que les informations s'affichent sur votre site.


== Installation ==


1. Téléversez le dossier de l'extension dans le dossier `/wp-content/plugins/` , ou installez l'extension depuis l'écran Extensions->Ajouter
2. Activez l'extension depuis l'écran 'Extensions' de WordPress
3. Utilisez le menu Réglages->Chalet Montagne pour configurer l'extension
4. Renseignez votre numéro de loueur et clé secrète (ce n'est pas votre mot de passe) puis validez


== Frequently Asked Questions ==

= Où puis-je trouver mon numéro de loueur ou ma clé secrète =

Votre numéro de loueur et votre clé secrète sont disponibles dans votre espace client Chalet Montagne (Rubrique Site-Perso / Outils)


== Screenshots ==

1. Ecran de validation de la clé
2. Liste des locations

== Changelog ==

= 2.7.8 =
* Compatibilité CSS theme WP

= 2.7.7 =
* Compatibilité CSS theme WP

= 2.7.6 =
* Compatibilité PHP 8 Bis

= 2.7.5 =
* Compatibilité PHP 8

= 2.7.4 =
* Compatibilité CSS

= 2.7.3 =
* Bug affichage mineur

= 2.7.2 =
* Bug maj serveur en PHP 7

= 2.7.1 =
* Mini Bug pour réglages par défault

= 2.7 =
* Ajout des réglages pour désactiver les formulaires de contact dans le planning ou les tarifs

= 2.6.14 =
* Correctif php 8

= 2.6.13 =
* Correctif version gratuite

= 2.6.12 =
* Compatibilité avec les Ical Multi TimeZone

= 2.6.11 =
* Correction detection tarif semaine & court séjour

= 2.6.9 =
* Correction fonction de récupération de données API

= 2.6.8 =
* Correction mise à jour auto du contenu ou média si l'abonnement a expiré

= 2.6.7 =
* Ajout fonctionnalité de vérification des données API

= 2.6.6 =
* Réenregistrement du temps de mise à jour d'une location et de l'url de contact si les données sont vides

= 2.6.5 =
* Corretif si mauvais certifica SSL

= 2.6.4 =
* Corretion lors de l'import d'un nouveau bien

= 2.6.3 =
* Correction synchronisation

= 2.6.2 =
* Affichage des images de la galerie dans l'ordre d'import

= 2.6.1 =
* Correction des paramètres de recherche des pages dont le contenu doit être mis à jour automatiquement
* Déplacement du code qui récupère les informations de mise à jour auto pour les transmettre à la vue paramètre

= 2.6 =
* Changement de lancement de vérification de migration de plugin_loaded vers wp_loaded
* Correction migration de l'ancien plugin payant vers ce plugin
* Correction de migration de contenu de l'ancien plugin payant vers ce plugin
* Correction de migration des images dans les galeries de l'ancien plugin payant vers ce plugin

= 2.5.4 =
* Correction ajout d'image à l'infini dans sur les locations dans le plug payant

= 2.5.1 =
* Correction traduction
* Correction CSS dans la fênetre modal ainsi que sur la liste des tarifs

= 2.5 =
* Correction Javascript et vue planning pour un affichage de deux calendriers sur la même page

= 2.4.11 =
* Test de l'abonnement avec une stockée.
* Modification fréquence d'appel à l'API

= 2.4.10 =
* Correction appel de l'API avec version et forçage manuel

= 2.4.9 =
* Correction ré import et remplacement automatique des contenus

= 2.4.8 =
* Correction arborescence suggérée à l'import des données
* Correction ré import manuel des photos

= 2.4.7 =
* Correction libellé

= 2.4.6 =
* Correction date de réservation modale courts séjours
* Création de la sous page Galerie pour un bien
* Ajustement des contenus
* Ajout de la page mentions légales par défaut dans le menu

= 2.4.5 =
* Blocage changement de date dans la fenêtre modale des courts séjours

= 2.4.4 =
* Correction dump intempestifs

= 2.4.3 =
* Mise à jour de la liste des dates de mises à jour des locations dans la base de données

= 2.4.2 =
* Correction du text du wizard d'installation
* Changement texte des shortcodes
* Correction modal lorsqu'utilisée pour la page contact

= 2.4.1 =
* Amélioration du wizard d'installation
* Correction affichage logo Chalet-Montagne sous les plannings et tarifs en version gratuite
* Affichage du shortcode de contact dans la page d'information
* Correction de vérification de menus

= 2.4 =
* Amélioration du wizard d'installation
* Création du shortcode pour générer une galerie
* Ajout d'options de mises à jour automatique dans la version payante

= 2.2 =
* Prise en compte du changement de délai entre deux mise à jour de planning et tarifs

= 2.1.11 =
* Correction CSS

= 2.1.10 =
* Correction date de mise à jour d'une location après synchronisation manuelle

= 2.1.9 =
* Correction affichage des tarifs sur les mois
* Ajout de l'année dans la grille de tarif

= 2.1.6 =
* Correction affichage des courts séjours dont la date de début et de fin s'étalent sur plusieurs mois

= 2.1.5 =
* Correction langue sur les calendriers
* Correction choix du bien à mettre à jour lors du lancement du CRON
* Correction suppression du menu lorsque le l'option n'est pas cochée et que le plugin est désactivé
* Création des pages lors de la réinstallation du plugin

= 2.1.4 =
* Correction fenêtre modal
* Correction cas particulier lors de l'installation
* Ajour de la mention "Offre par Chalet-Montagne" si le plugin est en version gratuite

= 2.1.3 =
* Correction migration du contenu

= 2.1.2 =
* Correction appel classe Chalet Montagne

= 2.1.1 =
* Modification CSS pour la largeur des images mises en avant

= 2.1 =
* Correction image mises en avant
* Correction shortcodes

= 2.0 =
* Fusion des plugins Chalet-Montagne Tools et Chalet-Montagne Privé.
* Migration des options Chalet-Montagne si l'on passe du plugin Chalet-Montagne Privé vers la version 2.0 du plugin Chalet-Montagne Tools
* Gestion des options d'administration si un abonnement payant a été souscrit auprès de Chalet-Montagne
* Tâches de fonds avec une fréquence différente si le plugin est utilisé en version gratuite ou payante
* Création des pages, arborescence et contenu si le plugin est utilisé en version payante

= 1.6.2 =
* Changement de droit pour accéder au menu chalet-montagne

= 1.6.1 =
* Ajout d'une mention "Service fourni par Chalet-Montagne.com" après chaque shortcode

= 1.5 =
* Correction Javascript sur le format des dates de la modal
* Correction placeholder dans la fenêtre modal

= 1.4 =
* Affichage des dernières minutes

= 1.3 =
* Correction mineures et securisation des repertoires

= 1.2 =
* Correction de l'affichage des courts séjours dans les tarifs

= 1.1 =
* Prise en charge des courts séjours dans la liste des tarifs et dans le calendrier

= 1.0 =
* Première version du plugin


