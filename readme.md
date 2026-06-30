Pré-requisitos e instalação

Para rodar o projeto é necessário ter instalado Docker, Docker Compose e Git. Primeiro deve ser feito o clone do repositório com git clone https://github.com/WladimirEmmanoel/teste-fullstack-wladimir-emmanoel e acesso à pasta do projeto. 
Em seguida, subir o ambiente com Docker usando docker compose up -d --build. 
Depois acessar o container da aplicação com docker compose exec app bash, instalar as dependências com composer install, configurar o .env e gerando a chave da aplicação com php artisan key:generate. 
Por fim, executar as migrations com php artisan migrate.

O projeto foi desenvolvido com uma arquitetura baseada em separação de responsabilidades, dividindo regras entre controllers, services e jobs. A sincronização de dados externos foi feita através de jobs para manter o processamento assíncrono e desacoplado. Os dados vindos de uma API externa são persistidos localmente para melhorar performance e permitir consultas analíticas mais eficientes.

Algumas melhorias não foram implementadas por limitação de tempo, como a criação de uma state machine para controle de status dos pedidos no backend, atualização em massa de status de pedidos via backend, melhorias de acessibilidade no frontend, melhorias de responsividade para telas pequenas, uma timeline de status mais visual e detalhada.