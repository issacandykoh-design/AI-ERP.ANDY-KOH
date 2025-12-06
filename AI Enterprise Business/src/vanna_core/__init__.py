"""
Vanna Core - SQL generation framework without web components
"""

__version__ = "0.1.0"

# Core base classes
from .base.base import VannaBase

# Core utilities
from .utils import validate_config_path

# Core types and exceptions
from .types import TrainingPlan, TrainingPlanItem
from .exceptions import DependencyError, ImproperlyConfigured, ValidationError

# LLM integrations
try:
    from .openai import OpenAI_Chat, OpenAI_Embeddings
except ImportError:
    pass

try:
    from .anthropic import Anthropic_Chat
except ImportError:
    pass

try:
    from .ollama import Ollama
except ImportError:
    pass

# Vector database integrations
try:
    from .chromadb import ChromaDB_VectorStore
except ImportError:
    pass

try:
    from .pinecone import PineconeDB_VectorStore
except ImportError:
    pass

try:
    from .qdrant import QdrantDB_VectorStore
except ImportError:
    pass

# Common combinations
class VannaDefault(ChromaDB_VectorStore, OpenAI_Chat):
    """Default Vanna configuration using ChromaDB and OpenAI"""
    def __init__(self, config=None):
        ChromaDB_VectorStore.__init__(self, config=config)
        OpenAI_Chat.__init__(self, config=config)

__all__ = [
    "VannaBase",
    "VannaDefault", 
    "validate_config_path",
    "TrainingPlan",
    "TrainingPlanItem",
    "DependencyError",
    "ImproperlyConfigured", 
    "ValidationError",
]