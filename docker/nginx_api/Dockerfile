FROM nginx:alpine

RUN apk --no-cache update \
  && apk --no-cache upgrade \
  && apk --no-cache add \
  && mkdir -p /var/www/app/public \
  && chmod 755 -R /var/www/app

COPY docker/nginx_api/etc/nginx/nginx.conf /etc/nginx/nginx.conf
