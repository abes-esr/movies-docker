FROM nginx:1.25

ARG MOVIES_WIKIBASE_URL_PUBLIQUE

COPY uploadsize.conf /etc/nginx/conf.d/uploadsize.conf
COPY orig.conf /tmp/orig.conf

RUN envsubst '$MOVIES_WIKIBASE_URL_PUBLIQUE' < /tmp/orig.conf > /etc/nginx/conf.d/wikibase.conf 