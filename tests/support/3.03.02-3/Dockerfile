FROM ubuntu:trusty

RUN apt-get -y update
RUN apt-get -y install tesseract-ocr=3.03.02-3
RUN apt-get -y install tesseract-ocr-deu tesseract-ocr-jpn tesseract-ocr-spa

ENTRYPOINT ["/usr/bin/tesseract"]
