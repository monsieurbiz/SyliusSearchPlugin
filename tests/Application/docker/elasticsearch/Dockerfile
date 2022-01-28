ARG ELASTICSEARCH_VERSION

FROM docker.elastic.co/elasticsearch/elasticsearch:$ELASTICSEARCH_VERSION

# Install ES plugins
RUN bin/elasticsearch-plugin install analysis-phonetic && \
    bin/elasticsearch-plugin install analysis-icu
