FROM node:20-alpine

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .

EXPOSE 5173

# Local development server only - no production build step, per the
# assignment's "local development setup is sufficient" note.
CMD ["npm", "run", "dev"]
