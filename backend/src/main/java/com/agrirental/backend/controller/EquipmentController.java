package com.agrirental.backend.controller;

import com.agrirental.backend.entity.Equipment;
import com.agrirental.backend.service.EquipmentService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/equipment")
@RequiredArgsConstructor
public class EquipmentController {
    
    private final EquipmentService equipmentService;

    @GetMapping
    public ResponseEntity<List<Equipment>> getAvailableEquipment() {
        return ResponseEntity.ok(equipmentService.getAllAvailableEquipment());
    }

    @PostMapping
    public ResponseEntity<Equipment> addEquipment(Authentication authentication, @RequestBody Equipment equipment) {
        return ResponseEntity.ok(equipmentService.addEquipment(authentication.getName(), equipment));
    }
    
    @GetMapping("/{id}")
    public ResponseEntity<Equipment> getEquipmentDetails(@PathVariable Long id) {
        return ResponseEntity.ok(equipmentService.getEquipmentDetails(id));
    }
}
