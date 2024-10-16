#!/bin/sh
set -euo pipefail

mkdir -p application/third_party/keys
openssl genpkey -algorithm RSA -out application/third_party/keys/rsa_1024_priv.pem -pkeyopt rsa_keygen_bits:1024
openssl rsa -in application/third_party/keys/rsa_1024_priv.pem -pubout -out application/third_party/keys/rsa_1024_pub.pem
