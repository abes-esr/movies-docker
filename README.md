# movies-docker

[![Docker Pulls](https://img.shields.io/docker/pulls/abesesr/movies.svg)](https://hub.docker.com/r/abesesr/movies/)

Movies-docker est un outil en charge de constituer une base de connaissance historique et centralisée sur les établissements de l'ESR et leurs activités en lien avec le doctorat et la documentation électronique. __Cet outil est destiné à un usage interne de l'Abes.__

![image](https://github.com/abes-esr/movies-docker/assets/43854599/461fa996-48f4-4669-92bb-c9678d26e2dc)

Ce dépôt contient la configuration docker 🐳 pour déployer l'application movies en local sur le poste d'un développeur, ou bien sur les serveurs de test et prod. 

## URLs de movies

Les URLs correspondantes aux déploiements en local, test et prod de movies sont les suivantes :

- local :
  - http://lap-TRI.levant.abes.fr : homepage de movies
  - http://lap-TRI.levant.abes.fr:11082/ : URL interne de l'adminer
- test :
  - https://movies-test.abes.fr : homepage de movies
  - https://diplotaxis5-test.v202.abes.fr:80/ : URL interne de movies
- prod
  - https://movies.abes.fr : homepage de movies
  - http://diplotaxis5-prod.v102.abes.fr:80/ : URL interne de movies

## Prérequis

Disposer de :
- ``docker``
- ``docker-compose``

## Installation

Déployer la configuration docker dans un répertoire :
```bash
# adaptez /opt/pod/ avec l'emplacement où vous souhaitez déployer l'application
cd /opt/pod/
git clone https://github.com/abes-esr/movies-docker.git
```

Configurer l'application depuis l'exemple du [fichier ``.env-dist``](./.env-dist) (ce fichier contient la liste des variables) :
```bash
cd /opt/pod/movies-docker/
cp .env-dist .env
# personnaliser alors le contenu du .env : indiquer les mots de passe et : le prefix de l'url (MOVIES_WIKIBASE_SCHEME), l'url publique (MOVIES_WIKIBASE_URL_PUBLIQUE), et le port du reverse proxy (MOVIES_PROXY_PORT).
```

**Note : les mots de passe ne sont pas présent dans le fichier au moment de la copie. Vous devez aller les renseigner manuellement en éditant le fichier dans la console avec nano par exemple**

Avant de démarrer l'application, assurez-vous que le fichier wikibase/LocalSettings.php ait les bonnes permissions. 
Pour ce faire, exécutez la commande suivante : 
```bash
cd /opt/pod/movies-docker/
chmod 644 wikibase/LocalSettings.php
```

Démarrer l'application :
```bash
cd /opt/pod/movies-docker/
docker-compose up --build -d
```

## Démarrage et arrêt

```bash
# pour démarrer l'application (ou pour appliquer des modifications 
# faites dans /opt/pod/movies-docker/.env)
cd /opt/pod/movies-docker/
docker-compose up -d
```

Remarque : retirer le ``-d`` pour voir passer les logs dans le terminal et utiliser alors CTRL+C pour stopper l'application

```bash
# pour stopper l'application
cd /opt/pod/movies-docker/
docker-compose stop


# pour redémarrer l'application
cd /opt/pod/movies-docker/
docker-compose restart
```

# pour injecter les propriétés et classes dans un wikibase vide : 
```bash
cd /opt/pod/movies-docker/
docker compose --profile oneshot up -d
ou 
docker compose run --rm movies_ephemere
```

## Supervision

```bash
# pour visualiser les logs de l'appli
cd /opt/pod/movies-docker/
docker-compose logs -f --tail=100
```

Cela va afficher les 100 dernière lignes de logs générées par l'application et toutes les suivantes jusqu'au CTRL+C qui stoppera l'affichage temps réel des logs.


## Configuration

Pour configurer l'application, vous devez créer et personnaliser un fichier ``/opt/pod/movies-docker/.env`` (cf section [Installation](#installation)). Les paramètres à placer dans ce fichier ``.env`` sont indiqués dans le fichier [``.env-dist``](https://github.com/abes-esr/movies-docker/blob/develop/.env-dist)

### Allocation de ressources pour les conteneurs

Pour ajuster l'allocation de ressources pour les conteneurs (par exemple, mémoire, CPU), vous pouvez définir la valeur des variables d'environnement suivantes dans votre fichier ``.env`` :

- `MOVIES_MEM_LIMIT`: Mémoire allouée au conteneur movies (par exemple: "512m" pour 512 Mo), valeur par défaut "5g".
- `MOVIES_MEMSWAP_LIMIT`: Quantité totale de mémoire et de swap que le conteneur est autorisé à utiliser. Si vous définissez cette valeur à 0, cela signifie que le swap est désactivé pour le conteneur.
- `MOVIES_CPU_LIMIT`: CPU alloué au conteneur movies (par exemple: "0.5" pour allouer 50% d'un CPU), valeur par défaut "5".

Ces valeurs ne sont que des exemples. Ajustez-les selon vos besoins et les ressources disponibles sur votre machine ou votre serveur.

## Déploiement continu

Les objectifs des déploiements continus de movies sont les suivants (cf [poldev](https://github.com/abes-esr/abes-politique-developpement/blob/main/01-Gestion%20du%20code%20source.md#utilisation-des-branches)) :
- git push sur la branche ``develop`` provoque un déploiement automatique sur le serveur ``diplotaxis5-dev``
- git push (le plus couramment merge) sur la branche ``main`` provoque un déploiement automatique sur le serveur ``diplotaxis5-test``
- git tag X.X.X (associé à une release) sur la branche ``main`` permet un déploiement (non automatique) sur le serveur ``diplotaxis5-prod``

Movies est déployé automatiquement en utilisant l'outil WatchTower. Pour permettre ce déploiement automatique avec WatchTower, il suffit de positionner à ``false`` la variable suivante dans le fichier ``/opt/pod/movies-docker/.env`` :
```env
MOVIES_WATCHTOWER_RUN_ONCE=false
```

Le fonctionnement de watchtower est de surveiller régulièrement l'éventuelle présence d'une nouvelle image docker de ``movies-wikibase``, si oui, de récupérer l'image en question, de stopper le ou les les vieux conteneurs et de créer le ou les conteneurs correspondants en réutilisant les mêmes paramètres ceux des vieux conteneurs. Pour le développeur, il lui suffit de faire un git commit+push par exemple sur la branche ``develop`` d'attendre que la github action build et publie l'image, puis que watchtower prenne la main pour que la modification soit disponible sur l'environnement cible, par exemple la machine ``diplotaxis5-dev``.

Le fait de passer ``MOVIES_WATCHTOWER_RUN_ONCE`` à false va faire en sorte d'exécuter périodiquement watchtower. Par défaut cette variable est à ``true`` car ce n'est pas utile voir cela peut générer du bruit dans le cas d'un déploiement sur un PC en local.

## Sauvegardes

Les éléments suivants sont à sauvegarder:
- ``/opt/pod/movies-docker/.env`` : contient la configuration spécifique de notre déploiement
- ``/docker-backup/movies/`` : contient les dumps quotidiens de la base de données maria-db de movies

### Restauration depuis une sauvegarde

Réinstallez l'application movies depuis la [procédure d'installation ci-dessus](#installation) et récupéré depuis les sauvegardes le fichier ``.env`` et placez le dans ``/opt/pod/movies-docker/.env`` sur la machine qui doit faire repartir movies.

Restaurez ensuite le dernier dump de la base de données postgresql de movies :
- récupérer le dernier dump généré par ``movies-db-dumper`` depuis le système de sauvegarde (le fichier dump ressemble à ceci ``mysql_all_movies_mysql_20230913-144637.sql.gz``) et placez le fichier dump récupéré (sans le décompresser) dans ``/docker-backup/movies/`` sur la machine qui doit faire repartir movies  

2 possiblités :  

1)  

- ensuite lancez uniquement les conteneurs ``movies-db`` et ``movies-db-dumper`` :
   ```bash
   docker-compose up -d movies-db movies-db-dumper
   ```
- lancez le script de restauration ``restore`` comme ceci et suivez les instructions :
   ```bash
   docker exec -it movies-db-dumper restore
   ```
- ou bien 
- C'est bon, la base de données movies est alors restaurée

2)  

Lancer la commande : 
```bash
cd /opt/pod/movies-docker/
zcat /docker-backup/movies/mysql_all_movies_mysql_XXXX-XXXX.sql.gz | sudo docker exec -u mysql -i movies_mysql mysql --user sqluser --password=XXXX
```

Lancez alors toute l'application movies et vérifiez qu'elle fonctionne bien :
```bash
cd /opt/pod/movies-docker/
docker-compose up -d
```

## Développements

### Admin de mariaDB
Pour consulter l'interface d'admin web de mariaDB (basée sur [Adminer](https://www.adminer.org/)) rendez vous sur cette URL : 
- local : http://127.0.0.1:11082/
- test : http://diplotaxis5-test.v202.abes.fr:11082/
- prod : http://diplotaxis5-prod.v102.abes.fr:11082/


### Mise à jour de la dernière version

Pour récupérer et démarrer la dernière version de l'application vous pouvez le faire manuellement comme ceci :
```bash
docker-compose pull
docker-compose up
```
Le ``pull`` aura pour effet de télécharger l'éventuelle dernière images docker disponible pour la version glissante en cours (ex: ``develop`` ou ``main``). Sans le pull c'est la dernière image téléchargée qui sera utilisée.

Ou bien [lancer le conteneur ``movies-watchtower``](https://github.com/abes-esr/movies-docker/blob/develop/README.md#d%C3%A9ploiement-continu) qui le fera automatiquement toutes les quelques secondes pour vous.

## Architecture

<img alt="schéma d'architecture" src="https://docs.google.com/drawings/d/e/2PACX-1vRfKzc04c7Pfjw4UvCkyww0OpTr6Fski_QHVGCKa9rwYUyWxbJlhCgjf8lDTi5pZ7ds4fpQ72g4mavm/pub?w=1134&amp;h=554">

([lien](https://docs.google.com/drawings/d/1hxpXxQbjK4eDCddP6yrQpWQwsmTWptCUwnM24tmKhPc/edit) pour modifier le schéma - droits requis)

Les codes de source de movies sont ici :
- https://github.com/abes-esr/movies-api : requêtes SPARQL pour Grlc
- https://github.com/abes-esr/movies-wikibase : conteneur wikibase avec plugin LDAP
