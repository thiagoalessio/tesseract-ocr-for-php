FROM debian:stretch
MAINTAINER thiagoalessio <thiagoalessio@me.com>

RUN apt-get -y update && apt-get -y install tesseract-ocr=3.04.01-5 --no-install-recommends
RUN /bin/bash -c "apt-get -y install tesseract-ocr-{deu,jpn,spa}=3.04.00-1 --no-install-recommends"

ENTRYPOINT ["/usr/bin/tesseract"]
