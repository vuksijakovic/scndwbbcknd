const http = require("http");
const { spawn } = require("child_process");
const httpProxy = require("http-proxy");

// Putanja do foldera sa PHP serverom
const phpPublicDir = "public";
const phpServerPort = 8000; // Port za PHP server
const nodeServerPort = 3000; // Port za Node.js proxy server

// Pokretanje PHP servera
console.log("Pokrećem PHP ugrađeni server...");
const phpServer = spawn("php", ["-S", `localhost:${phpServerPort}`, "-t", phpPublicDir]);

phpServer.stdout.on("data", (data) => {
    console.log(`PHP server: ${data}`);
});

phpServer.stderr.on("data", (data) => {
    console.error(`PHP server greška: ${data}`);
});

phpServer.on("close", (code) => {
    console.log(`PHP server se zatvorio sa kodom: ${code}`);
});

// Kreiranje proxy servera
const proxy = httpProxy.createProxyServer();

http.createServer((req, res) => {
    // Dodaj CORS zaglavlja
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type, Authorization");
    res.setHeader("content-type", "application/json")
    if (req.method === "OPTIONS") {
        res.writeHead(200);
        res.end();
        return;
    }

    // Prosledi zahtev PHP serveru
    proxy.web(req, res, { target: `http://localhost:${phpServerPort}` }, (error) => {
        console.error("Proxy greška:", error);
        res.writeHead(500, { "Content-Type": "text/plain" });
        res.end("Greška u povezivanju sa PHP serverom.");
    });
}).listen(nodeServerPort, () => {
    console.log(`Node.js proxy server radi na portu ${nodeServerPort}`);
});

process.on("SIGINT", () => {
    console.log("Zatvaram PHP server...");
    phpServer.kill();
    process.exit();
});
