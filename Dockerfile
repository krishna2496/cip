FROM registry.kubernetes.infra.optimy.net/tools/web-ci:7.3

COPY . /optimy
RUN rm /optimy/Dockerfile
# to do remove if not necessary
# RUN rm /optimy/Dockerfile.cli

# Install nodeJS
RUN apt-get install curl -y
RUN curl -sL https://deb.nodesource.com/setup_13.x | bash -
RUN apt-get install -y nodejs

# Install api-admin dependencies and change privileges of storage
RUN cd /optimy/admin-api && composer install
RUN cd /optimy/admin-api && npm install

# Install api dependencies and change privileges of storage
RUN cd /optimy/api && composer install
RUN cd /optimy/api && npm install

# Build access to frontend, api-doc and admin-api-doc
#RUN mkdir /optimy/public

# Frontend
#RUN mkdir /optimy/public/frontend
COPY ./frontend /tmp
RUN cd /tmp && mv vue-prod.config.js vue.config.js && npm install && npm rebuild node-sass && npm run build

RUN cp -R /tmp/dist/* /optimy/frontend/public
RUN cp /tmp/htaccess /optimy/frontend/public/.htaccess

RUN rm -rf /tmp/*

# Api-doc
ENV SWAGGER_API_JSON="/optimy/api-doc/cip-api.json"

# Admin-api-doc
ENV SWAGGER_ADMIN_API_JSON="/optimy/admin-api-doc/cip-admin-api.json"

RUN chown -R www-data:www-data /optimy/




