const http = require("http");
const { exec } = require("child_process");
const httpProxy = require("http-proxy");
const path = require("path");

// Putanja do Dockerfile
const dockerFilePath = path.resolve(__dirname, "Dockerfile");
const phpServerPort = 8000; // Port za PHP server
const nodeServerPort = 3000; // Port za Node.js proxy server
const dockerContainerName = "php-server-container"; // Naziv Docker kontejnera

// Kreiranje proxy servera
const proxy = httpProxy.createProxyServer();

http.createServer((req, res) => {
    // Dodaj CORS zaglavlja
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type, Authorization");
    res.setHeader("Content-Type", "application/json");

    if (req.method === "OPTIONS") {
        res.writeHead(200);
        res.end();
        return;
    }

    console.log(`Prosleđujem zahtev ka Docker kontejneru: ${dockerContainerName}`);
    console.log(`Metoda: ${req.method}`);
    console.log(`Zaglavlja:`, req.headers);

    let body = [];
    req.on("data", (chunk) => {
        body.push(chunk);
    });

    req.on("end", () => {
        body = Buffer.concat(body).toString();
        console.log(`Telo zahteva:`, body);

        // Docker build and run commands
        exec(`docker build -t php-server:latest -f ${dockerFilePath} .`, (buildErr) => {
            if (buildErr) {
                console.error(`Docker build error: ${buildErr.message}`);
                res.writeHead(500, { "Content-Type": "application/json" });
                res.end(JSON.stringify({ error: "Greška u Docker build procesu." }));
                return;
            }

            exec(`docker run -d -p ${phpServerPort}:${phpServerPort} --name ${dockerContainerName} php-server:latest`, (runErr) => {
                if (runErr) {
                    console.error(`Docker run error: ${runErr.message}`);
                    res.writeHead(500, { "Content-Type": "application/json" });
                    res.end(JSON.stringify({ error: "Greška u pokretanju Docker kontejnera." }));
                    return;
                }

                // Prosledi zahtev PHP serveru unutar Docker kontejnera
                const command = `docker exec ${dockerContainerName} curl -X ${req.method} -H "Content-Type: application/json" -d '${body}' http://php-server/graphql`;

                exec(command, (curlErr, stdout, stderr) => {
                    if (curlErr) {
                        console.error(`Curl error: ${curlErr.message}`);
                        res.writeHead(500, { "Content-Type": "application/json" });
                        res.end(JSON.stringify({ error: "Greška u povezivanju sa Docker kontejnerom." }));
                        return;
                    }

                    res.writeHead(200, { "Content-Type": "application/json" });
                    res.end(stdout);
                });
            });
        });
    });
}).listen(nodeServerPort, () => {
    console.log(`Node.js proxy server running on port ${nodeServerPort}`);
});

// Handle SIGINT to stop PHP Docker container
process.on("SIGINT", () => {
    console.log("Zatvaram Docker container...");
    exec(`docker stop ${dockerContainerName}`, (error) => {
        if (error) {
            console.error(`Error stopping Docker container: ${error.message}`);
        }
        process.exit();
    });
});
