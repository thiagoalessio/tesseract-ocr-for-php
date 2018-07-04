FROM debian:wheezy
MAINTAINER thiagoalessio <thiagoalessio@me.com>

RUN apt-get -y update && \
	apt-get -y install curl git-core php5-cli=5.4.45-0+deb7u14 \
	--no-install-recommends

RUN curl -sko- https://getcomposer.org/installer | \
	php -- --quiet --filename=composer --install-dir=/usr/local/bin
