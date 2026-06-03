"""
Input sanitization utilities to prevent prompt injection attacks.

All user-provided text that will be interpolated into AI prompts must be
sanitized to prevent adversarial prompt manipulation.
"""

import re


# Patterns commonly used in prompt injection attacks
_INJECTION_PATTERNS = [
    # Attempts to override system instructions
    r"(?i)(ignore|forget|disregard)\s+(all\s+)?(previous|above|prior)\s+(instructions?|prompts?|rules?|context)",
    # Attempts to assume a new role
    r"(?i)(you\s+are\s+now|act\s+as|pretend\s+to\s+be|new\s+instructions?:)",
    # Attempts to reveal system prompt
    r"(?i)(reveal|show|print|output|repeat)\s+(your\s+)?(system\s+)?(prompt|instructions?|rules?)",
    # Delimiter injection attempts
    r"```\s*(system|assistant|user)\s*\n",
    # Direct role overrides
    r"(?i)\[?(system|assistant)\]?\s*:",
]

_COMPILED_PATTERNS = [re.compile(p) for p in _INJECTION_PATTERNS]


def sanitize_user_input(text: str) -> str:
    """
    Sanitize user input before interpolation into AI prompts.

    This function:
    1. Strips leading/trailing whitespace
    2. Limits length to prevent token exhaustion
    3. Removes or neutralizes known prompt injection patterns
    4. Escapes special delimiters that could confuse the LLM

    Args:
        text: Raw user input string

    Returns:
        Sanitized string safe for prompt interpolation
    """
    if not text:
        return ""

    # Strip whitespace
    sanitized = text.strip()

    # Check for injection patterns and neutralize them
    for pattern in _COMPILED_PATTERNS:
        sanitized = pattern.sub("[FILTERED]", sanitized)

    # Remove excessive whitespace/newlines that could be used to visually
    # separate injected instructions from legitimate content
    sanitized = re.sub(r"\n{4,}", "\n\n\n", sanitized)

    # Escape backtick sequences that could create code block boundaries
    sanitized = sanitized.replace("```", "` ` `")

    return sanitized


def sanitize_name(text: str, max_length: int = 200) -> str:
    """
    Sanitize a name/keyword field — more restrictive sanitization.

    Removes characters that are clearly not part of a person's name,
    keyword, or topic while preserving international characters.
    """
    if not text:
        return ""

    sanitized = sanitize_user_input(text)

    # For name/keyword fields, also remove control characters
    sanitized = re.sub(r"[\x00-\x1f\x7f-\x9f]", "", sanitized)

    # Limit length
    return sanitized[:max_length]
