FROM node:lts as builder

LABEL maintainer="roman.ondracek@iqrf.com"

WORKDIR /app

COPY . /app
RUN sed -i "s/\t\"commit\"\: .*/\t\"commit\"\: \"`git rev-parse --verify HEAD`\",/" version.json
RUN npm install
RUN npm run build

FROM arm32v7/nginx:stable

LABEL maintainer="roman.ondracek@iqrf.com"

COPY --from=builder /app/www/dist /app
COPY docker/frontend/config/nginx.conf /etc/nginx/conf.d/default.conf

CMD ["nginx", "-g", "daemon off;"]

EXPOSE 80 443
