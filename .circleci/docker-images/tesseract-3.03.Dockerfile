FROM docker.io/library/fedora:22

RUN dnf install -y curl git php-cli php-pecl-xdebug unzip \
	tesseract-3.03-0.4.rc1.fc22 \
	tesseract-langpack-deu \
	tesseract-langpack-jpn \
	tesseract-langpack-spa &&\
	dnf clean all && rm -rf /var/cache/yum && rm -rf /var/tmp/yum-*

RUN curl -sko- https://getcomposer.org/installer |\
	php -- --quiet --filename=composer --install-dir=/usr/local/bin

ENTRYPOINT ["/bin/bash"]
