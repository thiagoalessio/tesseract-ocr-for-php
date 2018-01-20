FROM debian:jessie
MAINTAINER thiagoalessio <thiagoalessio@me.com>

RUN apt-get -y update && apt-get -y install tesseract-ocr=3.03.03-1 --no-install-recommends
RUN /bin/bash -c "apt-get -y install tesseract-ocr-{deu,jpn,spa}=3.02-2 --no-install-recommends"

ENTRYPOINT ["/usr/bin/tesseract"]
