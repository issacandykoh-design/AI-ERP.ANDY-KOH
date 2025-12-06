# Vanna Core

Vanna Core is a streamlined version of the Vanna SQL generation framework that focuses on core functionality without web application components.

## Features

- **SQL Generation**: Generate SQL queries from natural language using LLMs
- **Multiple LLM Support**: OpenAI, Anthropic, Ollama
- **Vector Database Integration**: ChromaDB, Pinecone, Qdrant
- **Training System**: Train on DDL, documentation, and question-SQL pairs
- **Database Connectivity**: Support for PostgreSQL, MySQL, SQLite
- **No Web Dependencies**: Pure Python library without Flask/web components

## Installation

```bash
pip install vanna-core
```

### Optional Dependencies

Install specific LLM or vector database support:

```bash
# For OpenAI
pip install vanna-core[openai]

# For ChromaDB
pip install vanna-core[chromadb]

# For all optional dependencies
pip install vanna-core[all]
```

## Quick Start

```python
from vanna_core import VannaDefault

# Initialize with default configuration (ChromaDB + OpenAI)
vn = VannaDefault()

# Train the model
vn.train(ddl="CREATE TABLE customers (id INT, name VARCHAR(100))")
vn.train(question="What are the customer names?", sql="SELECT name FROM customers")

# Generate SQL
sql = vn.generate_sql("Show me all customers")
print(sql)

# Execute SQL (if database is connected)
df = vn.run_sql(sql)
print(df)
```

## Custom Configuration

```python
from vanna_core.openai import OpenAI_Chat
from vanna_core.chromadb import ChromaDB_VectorStore

class MyVanna(ChromaDB_VectorStore, OpenAI_Chat):
    def __init__(self, config=None):
        ChromaDB_VectorStore.__init__(self, config=config)
        OpenAI_Chat.__init__(self, config=config)

vn = MyVanna(config={'api_key': 'your-openai-key'})
```

## Differences from Full Vanna

This core version removes:
- Flask web application
- REST API endpoints
- Web UI components
- WebSocket support
- Authentication middleware

Retained features:
- All SQL generation capabilities
- LLM integrations
- Vector database support
- Training functionality
- Database connectivity
- Visualization (Plotly)

## License

MIT License