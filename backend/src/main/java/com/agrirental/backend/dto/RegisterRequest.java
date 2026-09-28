package com.agrirental.backend.dto;

import lombok.Data;
import lombok.Builder;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;
import com.agrirental.backend.entity.enums.UserRole;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class RegisterRequest {
    private String name;
    private String mobile;
    private String email;
    private String password;
    private UserRole role;
    private String city;
    private String state;
    private String address;
}
