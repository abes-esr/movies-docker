#!/bin/bash
#https://www.mediawiki.org/wiki/User:XZise/Login_with_cURL
#https://www.baeldung.com/linux/csv-parsing
NAME=$NAME
PASS=$PASS

BOTNAME=$BOTNAME
BOTPASS=$BOTPASS

API='http://'$MOVIES_WIKIBASE_HOST'/w/api.php'
echo $API
echo $NAME
LGTOKEN=$(curl -b cookie.lwp -c cookie.lwp -d action=login -d lgname=$NAME -d lgpassword=$PASS -d format=json $API | jq '.login.token' -r)
curl -b cookie.lwp -c cookie.lwp -d action=login -d lgname=$NAME -d lgpassword=$PASS --data-urlencode lgtoken=$LGTOKEN -d format=json $API
echo "LGTOKEN : ${LGTOKEN}"
CSRFTOKEN=$(curl -b cookie.lwp -c cookie.lwp "${API}?action=query&meta=tokens&format=json" | jq '.query.tokens.csrftoken' -r)
echo "CSRFTOKEN : ${CSRFTOKEN}"

echo "Création des propriétés"
while IFS="|" read -r rec_label rec_type
do
  PROPERTY='{"labels":{"fr":{"language":"fr","value":"'$rec_label'"}},"datatype":"'$rec_type'"}'  
   echo $PROPERTY
   curl -b cookie.lwp -c cookie.lwp -d action=wbeditentity -d new=property -d data="$PROPERTY" --data-urlencode token=$CSRFTOKEN -d format=json $API
done < /home/data/properties.csv

echo "Création des classes"
while read rec_label
do
ITEM='{"labels":{"fr":{"language":"fr","value":"'$rec_label'"}}}'  
   echo $ITEM
   curl -b cookie.lwp -c cookie.lwp -d action=wbeditentity -d new=item -d data="$ITEM" --data-urlencode token=$CSRFTOKEN -d format=json $API
done < /home/data/items.csv

echo "Création des relations"
while IFS="|" read -r rec_enfant rec_parent
do
   #P2 est la propriété "sous classe de"
   CLAIM='{"claims":[{"mainsnak":{"snaktype":"value","property":"P2","datavalue":{"value":{"id": "'$rec_parent'"},"type":"wikibase-entityid"}},"type":"statement","rank":"normal"}]}'  
   echo $CLAIM
   curl -b cookie.lwp -c cookie.lwp -d action=wbeditentity -d id=$rec_enfant -d data="$CLAIM" --data-urlencode token=$CSRFTOKEN -d format=json $API
done < /home/data/claims.csv

echo "Création des tags"
while read rec_tag
do
   echo $rec_tag
   curl -b cookie.lwp -c cookie.lwp -d action=managetags -d operation=create -d tag=$rec_tag --data-urlencode token=$CSRFTOKEN -d format=json $API
done < /home/data/tags.csv

echo "Création du compte Bot ${BOTNAME}"
CREATETOKEN=$(curl -b cookie.lwp -c cookie.lwp "${API}?action=query&meta=tokens&type=createaccount&format=json" | jq '.query.tokens.createaccounttoken' -r)
echo "CREATETOKEN : ${CREATETOKEN}"
curl -b cookie.lwp -c cookie.lwp -d action=createaccount -d username=$BOTNAME -d password=$BOTPASS -d retype=$BOTPASS -d createreturnurl=$API --data-urlencode createtoken=$CREATETOKEN -d format=json $API

echo "Ajout du compte ${BOTNAME}, dans le groupe Bots"
USERRIGHTSTOKEN=$(curl -b cookie.lwp -c cookie.lwp "${API}?action=query&meta=tokens&type=userrights&format=json" | jq '.query.tokens.userrightstoken' -r)
echo "USERRIGHTSTOKEN : ${USERRIGHTSTOKEN}"
curl -b cookie.lwp -c cookie.lwp -d action=userrights -d user=$BOTNAME -d add=bots --data-urlencode token=$USERRIGHTSTOKEN -d format=json $API
