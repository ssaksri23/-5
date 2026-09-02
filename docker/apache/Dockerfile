# Prebuilt image with gd, mysqli, pdo_mysql, zip and mod_rewrite already
# compiled in, so the build needs no OS package installs.
FROM mirror.gcr.io/webdevops/php-apache:8.1

# GNUBoard5 source is vendored under ./www in this repository.
COPY --chown=application:application www/ /app/

# data/ is excluded from the vendored source (install-time generated) and is
# usually a fresh named volume, which Docker mounts owned by root unless the
# image already has content there — so create it here with the right owner.
RUN mkdir -p /app/data/cache && chown -R application:application /app/data
