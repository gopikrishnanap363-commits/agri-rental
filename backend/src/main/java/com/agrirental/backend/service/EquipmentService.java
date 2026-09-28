package com.agrirental.backend.service;

import com.agrirental.backend.entity.Equipment;
import com.agrirental.backend.entity.User;
import com.agrirental.backend.entity.enums.EquipmentApprovalStatus;
import com.agrirental.backend.entity.enums.EquipmentAvailabilityStatus;
import com.agrirental.backend.repository.EquipmentRepository;
import com.agrirental.backend.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

import java.util.List;

@Service
@RequiredArgsConstructor
public class EquipmentService {
    
    private final EquipmentRepository equipmentRepository;
    private final UserRepository userRepository;
    
    public List<Equipment> getAllAvailableEquipment() {
        return equipmentRepository.findByAvailabilityStatus(EquipmentAvailabilityStatus.AVAILABLE);
    }
    
    public Equipment addEquipment(String ownerMobile, Equipment equipment) {
        User owner = userRepository.findByMobile(ownerMobile)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Owner not found"));
            
        equipment.setOwner(owner);
        equipment.setApprovalStatus(EquipmentApprovalStatus.PENDING);
        equipment.setAvailabilityStatus(EquipmentAvailabilityStatus.AVAILABLE);
        return equipmentRepository.save(equipment);
    }
    
    public Equipment getEquipmentDetails(Long id) {
        return equipmentRepository.findById(id)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Equipment not found"));
    }
}
