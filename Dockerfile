# Define PHP version
ARG TARGET_PHP_VERSION=7.3

# Define PHP docker image
FROM php:${TARGET_PHP_VERSION}-cli

MAINTAINER Adam Kadlec <adam.kadlec@fastybird.com>

################################
# CONTAINER REQUIRED ARGUMENTS #
################################

# App instalation folder
ARG APP_CODE_PATH=/usr/src/app
# Container default timezone
ARG APP_TZ=UTC

###########################
# CONTAINER CONFIGURATION #
###########################

# Set server timezone
RUN ln -snf /usr/share/zoneinfo/${APP_TZ} /etc/localtime && echo ${APP_TZ} > /etc/timezone

RUN apt-get update -yqq \
 && apt-get install -yqq \
 build-essential \
 autoconf \
 curl \
 dnsutils \
 git \
 wget \
 nano \
 unzip \
 zip \
 bzip2 \
;

RUN docker-php-ext-install \
 mysqli \
 pdo \
 pdo_mysql \
;

###########################
# SUPERVISOR INSTALLATION #
###########################

# Install supervisor
RUN apt-get update -yqq && apt-get install -yqq supervisor

COPY ./resources/supervisor/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

##############################
# IO SERVER APP INSTALLATION #
##############################

ADD ./config ${APP_CODE_PATH}/config
ADD ./resources ${APP_CODE_PATH}/resources
ADD ./src ${APP_CODE_PATH}/src
ADD ./var ${APP_CODE_PATH}/var
COPY ./composer.json ${APP_CODE_PATH}/

# Install composer installer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Checkout & install server app
RUN cd ${APP_CODE_PATH} \
 && composer install --no-dev \
 && composer clearcache \
;

#####################################
# FINISHING CONTAINER CONFIGURATION #
#####################################

WORKDIR "${APP_CODE_PATH}"

# Supervisord run command
CMD ["/usr/bin/supervisord"]
