FROM ubuntu:xenial

RUN apt-get -y update
RUN apt-get -y install tesseract-ocr=3.04.01-4
RUN apt-get -y install tesseract-ocr-deu tesseract-ocr-jpn tesseract-ocr-spa

ENTRYPOINT ["/usr/bin/tesseract"]
