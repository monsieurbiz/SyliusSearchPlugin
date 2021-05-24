FROM docker.elastic.co/elasticsearch/elasticsearch:7.12.1

RUN elasticsearch-plugin install --batch analysis-icu && \
    elasticsearch-plugin install --batch analysis-phonetic
