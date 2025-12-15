# Architecture Documentation

## Overview

The Zindagi Z-Connect package follows a modular, scalable architecture designed for banking operations. It is built with security, auditability, and maintainability as core principles.

## Architecture Principles

1. **Modularity**: Each banking solution (Onboarding, Inquiry, Payment, Lending) is a separate module
2. **Separation of Concerns**: Clear separation between Services, Repositories, Models, and DTOs
3. **Dependency Injection**: All dependencies are injected via interfaces
4. **Event-Driven**: Key operations trigger events for extensibility
5. **Auditability**: All operations are logged and auditable
6. **Security**: Sensitive data is masked, requests are validated

## Package Structure

```
zindagi-zconnect/
├── config/
│   └── zindagi-zconnect.php          # Package configuration
├── src/
│   ├── Modules/                      # Business modules
│   │   ├── Onboarding/               # Onboarding Solution
│   │   │   ├── Controllers/          # HTTP controllers
│   │   │   ├── DTOs/                 # Data Transfer Objects
│   │   │   ├── Events/               # Domain events
│   │   │   ├── Models/               # Eloquent models
│   │   │   ├── Repositories/         # Data access layer
│   │   │   └── Services/             # Business logic
│   │   ├── Inquiry/                  # Inquiry Solution
│   │   ├── Payment/                  # Payment Solution
│   │   └── Lending/                  # Lending Solution
│   ├── Services/                     # Core services
│   │   ├── AuthenticationService     # Token management
│   │   ├── HttpClientService         # HTTP client with auth
│   │   ├── LoggingService            # Comprehensive logging
│   │   └── AuditService              # Audit trail
│   ├── Repositories/                 # Data repositories
│   ├── Models/                       # Shared models
│   ├── Middleware/                   # Security middleware
│   ├── Helpers/                      # Utility helpers
│   ├── Exceptions/                   # Custom exceptions
│   ├── Facades/                      # Laravel facades
│   ├── Providers/                    # Service providers
│   └── database/
│       └── migrations/               # Database migrations
└── tests/                            # Test suite
```

## Core Components

### Services Layer

**AuthenticationService**
- Manages API authentication tokens
- Handles token caching and refresh
- Creates its own HTTP client for auth requests (avoids circular dependency)

**HttpClientService**
- Wraps Guzzle HTTP client
- Automatically adds authentication headers
- Logs all requests and responses
- Creates audit trail entries

**LoggingService**
- Comprehensive logging with configurable channels
- Automatic sensitive data masking
- Request/response logging
- Error logging with context

**AuditService**
- Creates audit trail entries for all operations
- Queryable audit logs
- Tracks user, IP, and action details

### Module Structure (Onboarding Example)

Each module follows this structure:

1. **DTOs**: Data Transfer Objects for request/response
2. **Services**: Business logic and API integration
3. **Repositories**: Data access abstraction
4. **Models**: Eloquent models for database operations
5. **Events**: Domain events for extensibility
6. **Controllers**: HTTP controllers (optional, for direct API usage)

### Data Flow

```
Request → Controller → Service → Repository → Model → Database
                ↓
            HTTP Client → API
                ↓
            Logging + Audit
                ↓
            Event Dispatch
```

## Security Features

1. **Token Management**: Secure token caching and refresh
2. **Data Masking**: Automatic masking of sensitive fields in logs
3. **Request Validation**: Middleware for API key and signature validation
4. **SSL Verification**: Configurable SSL certificate verification
5. **Audit Trail**: Complete audit trail for compliance

## Extensibility

### Adding New Modules

To add a new module (e.g., Inquiry):

1. Create module directory: `src/Modules/Inquiry/`
2. Create DTOs, Services, Repositories, Models, Events
3. Register service in `ZindagiZconnectServiceProvider`
4. Add module to main `ZindagiZconnect` class
5. Create migrations
6. Add tests

### Event Listeners

All modules fire events that can be listened to:

```php
Event::listen(OnboardingInitiated::class, function ($event) {
    // Custom logic
});
```

## Testing Strategy

- **Unit Tests**: Test individual components in isolation
- **Integration Tests**: Test module interactions
- **Feature Tests**: Test complete workflows

## Performance Considerations

1. **Token Caching**: Tokens are cached to reduce API calls
2. **Lazy Loading**: Services are loaded only when needed
3. **Database Indexing**: Proper indexes on audit and onboarding tables
4. **Log Rotation**: Configurable log channels for performance

## Future Enhancements

- [ ] Inquiry Solution module
- [ ] Payment Solution module
- [ ] Lending Solution module
- [ ] Queue support for async operations
- [ ] Webhook support
- [ ] Rate limiting
- [ ] Retry mechanisms with exponential backoff