FROM docker.io/library/fedora:25

RUN dnf install -y curl git php-cli php-pecl-xdebug php-xml unzip \
	tesseract-3.04.01-2.fc25 \
	tesseract-langpack-deu \
	tesseract-langpack-jpn \
	tesseract-langpack-spa &&\
	dnf clean all && rm -rf /var/cache/yum && rm -rf /var/tmp/yum-*

RUN curl -sko- https://getcomposer.org/installer |\
	php -- --quiet --filename=composer --install-dir=/usr/local/bin

ENTRYPOINT ["/bin/bash"]
