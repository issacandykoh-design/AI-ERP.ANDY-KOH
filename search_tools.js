const { Client } = require("@modelcontextprotocol/sdk/client/index.js");
const { StdioClientTransport } = require("@modelcontextprotocol/sdk/client/stdio.js");

const transport = new StdioClientTransport({
    command: "npx",
    args: ["-y", "mcp-remote", "https://rube.app/mcp"]
});

const client = new Client({
    name: "test-client",
    version: "1.0.0"
}, {
    capabilities: {}
});

async function main() {
    await client.connect(transport);
    
    console.log("Searching for Facebook tools...");
    const fbResult = await client.callTool("RUBE_SEARCH_TOOLS", {
        queries: ["post to facebook page", "list facebook pages"],
        use_case: "I need to post images to a facebook page"
    });
    console.log("Facebook Tools Result:", JSON.stringify(fbResult, null, 2));

    console.log("Searching for Image Generation tools...");
    const imgResult = await client.callTool("RUBE_SEARCH_TOOLS", {
        queries: ["generate image", "create image"],
        use_case: "I need to generate images using AI"
    });
    console.log("Image Tools Result:", JSON.stringify(imgResult, null, 2));

    process.exit(0);
}

main().catch(console.error);
