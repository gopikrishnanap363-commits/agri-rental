package com.agrirental.backend.service;

import com.agrirental.backend.dto.AuthResponse;
import com.agrirental.backend.dto.LoginRequest;
import com.agrirental.backend.dto.RegisterRequest;
import com.agrirental.backend.dto.UserResponse;
import com.agrirental.backend.entity.User;
import com.agrirental.backend.entity.enums.UserRole;
import com.agrirental.backend.entity.enums.UserStatus;
import com.agrirental.backend.repository.UserRepository;
import com.agrirental.backend.security.CustomUserDetails;
import com.agrirental.backend.security.JwtService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

@Service
@RequiredArgsConstructor
public class AuthService {

    private final UserRepository userRepository;
    private final PasswordEncoder passwordEncoder;
    private final JwtService jwtService;
    private final AuthenticationManager authenticationManager;

    public AuthResponse register(RegisterRequest request) {
        if (request.getRole() == UserRole.ADMIN) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Cannot register as ADMIN directly");
        }
        
        if (request.getRole() == null) {
            request.setRole(UserRole.FARMER);
        }

        if (userRepository.existsByMobile(request.getMobile())) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Mobile number already exists");
        }

        if (request.getEmail() != null && !request.getEmail().isEmpty() && userRepository.existsByEmail(request.getEmail())) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Email already exists");
        }

        User user = new User();
        user.setName(request.getName());
        user.setMobile(request.getMobile());
        user.setEmail(request.getEmail());
        user.setPasswordHash(passwordEncoder.encode(request.getPassword()));
        user.setRole(request.getRole());
        user.setStatus(UserStatus.ACTIVE);
        user.setCity(request.getCity());
        user.setState(request.getState());
        user.setAddress(request.getAddress());
        user.setIsVerified(false);

        var savedUser = userRepository.save(user);
        
        CustomUserDetails userDetails = new CustomUserDetails(savedUser);
        var jwtToken = jwtService.generateToken(userDetails, savedUser.getId(), savedUser.getRole().name());

        return AuthResponse.builder()
                .token(jwtToken)
                .user(mapToUserResponse(savedUser))
                .build();
    }

    public AuthResponse login(LoginRequest request) {
        authenticationManager.authenticate(
                new UsernamePasswordAuthenticationToken(
                        request.getMobile(),
                        request.getPassword()
                )
        );
        
        var user = userRepository.findByMobile(request.getMobile())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "User not found"));
                
        if (user.getStatus() != UserStatus.ACTIVE) {
            throw new ResponseStatusException(HttpStatus.FORBIDDEN, "Account is deactivated");
        }

        CustomUserDetails userDetails = new CustomUserDetails(user);
        var jwtToken = jwtService.generateToken(userDetails, user.getId(), user.getRole().name());

        return AuthResponse.builder()
                .token(jwtToken)
                .user(mapToUserResponse(user))
                .build();
    }
    
    private UserResponse mapToUserResponse(User user) {
        return UserResponse.builder()
                .id(user.getId())
                .name(user.getName())
                .mobile(user.getMobile())
                .email(user.getEmail())
                .role(user.getRole())
                .build();
    }
}
