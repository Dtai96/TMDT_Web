import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';

const orderStatuses = ['Chờ xác nhận', 'Chờ khách đồng ý', 'Đã xác nhận', 'Đang giao', 'Đã giao', 'Đã hủy'];
const leadStatuses = ['Mới tiếp nhận', 'Đang trao đổi', 'Đã báo giá', 'Hoàn tất', 'Đã hủy'];
const complaintStatuses = ['Mới tiếp nhận', 'Đang xác minh', 'Đã phản hồi', 'Hoàn tất'];
const money = value => new Intl.NumberFormat('vi-VN').format(value) + 'đ';

export default function TradeManagement({ apiBase, token, kind }) {
  const [records, setRecords] = useState([]);
  const [selected, setSelected] = useState(null);
  const [status, setStatus] = useState('');
  const [fee, setFee] = useState('');
  const [quote, setQuote] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const endpoint = kind === 'orders' ? 'orders' : kind === 'complaints' ? 'complaints' : 'wholesale';

  async function request(path, options = {}) {
    const response = await fetch(`${apiBase}/management/${path}`, { ...options, headers: { Accept: 'application/json', 'Content-Type': 'application/json', Authorization: `Bearer ${token}` } });
    const result = await response.json();
    if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || result.message || 'Không thể thực hiện yêu cầu.');
    return result;
  }
  async function load() {
    setLoading(true); setError('');
    try { setRecords(await request(endpoint)); } catch (err) { setError(err.message || 'Không tải được dữ liệu.'); }
    finally { setLoading(false); }
  }
  useEffect(() => { setSelected(null); setNotice(''); load(); }, [kind, token]);
  function select(record) {
    setSelected(record); setStatus(record.status); setFee(record.shipping_fee == null ? '' : String(record.shipping_fee)); setQuote(kind === 'complaints' ? (record.resolution || '') : (record.quote_details || '')); setError(''); setNotice('');
  }
  async function save() {
    if (kind === 'orders' && fee !== '' && !/^\d+$/.test(fee)) { setError('Phí giao phải là số nguyên không âm.'); return; }
    setLoading(true); setError(''); setNotice('');
    try {
      const body = kind === 'orders' ? { status, shipping_fee: fee === '' ? null : Number(fee) } : kind === 'complaints' ? { status, resolution: quote || null } : { status, quote_details: quote || null };
      const updated = await request(`${endpoint}/${selected.id}`, { method: 'PUT', body: JSON.stringify(body) });
      setSelected(updated); setRecords(previous => previous.map(item => item.id === updated.id ? updated : item)); setNotice('Đã lưu nội bộ. Trại cần liên hệ khách để thông báo và xác nhận thỏa thuận.');
    } catch (err) { setError(err.message || 'Không thể lưu.'); } finally { setLoading(false); }
  }

  return <ScrollView style={s.scroll} keyboardShouldPersistTaps="handled" contentContainerStyle={s.container}>
    <Text style={s.heading}>{kind === 'orders' ? 'Đơn hàng D2C' : kind === 'complaints' ? 'Phản ánh và khiếu nại' : 'Yêu cầu báo giá B2B'}</Text>
    <Text style={s.muted}>{kind === 'orders' ? 'Chỉ chuyển trạng thái đã xác nhận sau khi khách đồng ý phí giao và tổng tiền.' : kind === 'complaints' ? 'Ghi lại quá trình xác minh và liên hệ khách để thông báo phương án xử lý.' : 'Báo giá được lưu nội bộ. Trại cần liên hệ và thỏa thuận trực tiếp với đối tác.'}</Text>
    {loading ? <ActivityIndicator color="#183d2b" /> : null}{error ? <Text style={s.error}>{error}</Text> : null}{notice ? <Text style={s.notice}>{notice}</Text> : null}
    {selected ? <View style={s.detail}>
      <Pressable onPress={() => { setSelected(null); setError(''); setNotice(''); }}><Text style={s.link}>← Quay lại danh sách</Text></Pressable>
      {kind === 'orders' ? <>
        <Text style={s.heading}>{selected.code}</Text><Text style={s.body}>Khách: {selected.customer_name} · {selected.phone}</Text><Text style={s.body}>Địa chỉ: {selected.address}</Text><Text style={s.body}>Mong muốn: {selected.desired_time || 'Chưa ghi'}</Text><Text style={s.body}>Thanh toán: {selected.payment_method === 'cod' ? 'COD' : 'Chuyển khoản sau xác nhận'}</Text><Text style={s.body}>Ghi chú: {selected.note || 'Không có'}</Text>
        {(selected.items || []).map(item => <Text key={item.id} style={s.body}>{item.product_name} × {item.quantity} · {money(item.line_total)}</Text>)}
        <Text style={s.body}>Tiền hàng: {money(selected.subtotal)}</Text><Text style={s.label}>Phí giao hàng đã trao đổi (đ)</Text><TextInput value={fee} onChangeText={setFee} keyboardType="numeric" placeholder="Để trống nếu chưa xác nhận" style={s.input} /><Text style={s.muted}>Tổng khi phí được ghi nhận: {fee !== '' && /^\d+$/.test(fee) ? money(selected.subtotal + Number(fee)) : 'Chưa xác định'}</Text>
      </> : kind === 'complaints' ? <>
        <Text style={s.heading}>{selected.code}</Text><Text style={s.body}>Khách: {selected.name} · {selected.phone}</Text><Text style={s.body}>Mã đơn: {selected.order_code || 'Chưa ghi'}</Text><Text style={s.body}>Thời điểm nhận: {selected.received_at || 'Chưa ghi'}</Text><Text style={s.body}>Nội dung: {selected.description}</Text><Text style={s.label}>Phương án và kết quả xử lý</Text><TextInput value={quote} onChangeText={setQuote} multiline placeholder="Ghi lại việc xác minh và phương án đã trao đổi…" style={[s.input, s.multiline]} />
      </> : <>
        <Text style={s.heading}>{selected.reference || `Yêu cầu #${selected.id}`}</Text><Text style={s.body}>Đơn vị: {selected.business_name}</Text><Text style={s.body}>Liên hệ: {selected.contact_name} · {selected.phone}</Text><Text style={s.body}>Email: {selected.email || 'Không có'}</Text><Text style={s.body}>Giao tại: {selected.delivery_location || 'Chưa ghi'}</Text><Text style={s.body}>Sản lượng: {selected.monthly_volume} · Tần suất: {selected.purchase_frequency || 'Chưa ghi'}</Text><Text style={s.body}>Nhu cầu: {selected.message || 'Chưa ghi'}</Text>
        <Text style={s.label}>Nội dung báo giá / thỏa thuận</Text><TextInput value={quote} onChangeText={setQuote} multiline placeholder="Sản phẩm, đơn giá, số lượng tối thiểu, hiệu lực, giao hàng và thanh toán…" style={[s.input, s.multiline]} />
      </>}
      <Text style={s.label}>Trạng thái</Text><View style={s.statuses}>{(kind === 'orders' ? orderStatuses : kind === 'complaints' ? complaintStatuses : leadStatuses).map(item => <Pressable key={item} onPress={() => setStatus(item)} style={[s.status, item === status && s.selected]}><Text style={s.body}>{item}</Text></Pressable>)}</View><Pressable disabled={loading} onPress={save} style={s.button}><Text style={s.buttonText}>Lưu xử lý</Text></Pressable>
    </View> : <>
      {!loading && records.length === 0 ? <Text style={s.muted}>Chưa có dữ liệu.</Text> : null}
      {records.map(record => <Pressable key={record.id} onPress={() => select(record)} style={s.item}><Text style={s.name}>{kind === 'orders' || kind === 'complaints' ? record.code : (record.reference || `Yêu cầu #${record.id}`)}</Text><Text style={s.body}>{kind === 'orders' ? record.customer_name : kind === 'complaints' ? record.name : record.business_name} · {record.status}</Text><Text style={s.link}>Xem và xử lý →</Text></Pressable>)}
    </>}
  </ScrollView>;
}

const s = StyleSheet.create({ scroll:{flex:1,minHeight:0},container:{paddingBottom:24,gap:12},heading:{fontSize:18,fontWeight:'700',color:'#183d2b',marginTop:8},muted:{fontSize:13,lineHeight:20,color:'#637264'},body:{fontSize:13,lineHeight:21,color:'#34483a'},label:{fontSize:13,fontWeight:'700',color:'#183d2b',marginTop:12,marginBottom:4},input:{borderWidth:1,borderColor:'#cbd7c9',padding:10,minHeight:42,fontSize:14,color:'#1d2e22'},multiline:{minHeight:110,textAlignVertical:'top'},statuses:{flexDirection:'row',flexWrap:'wrap',gap:6,marginBottom:15},status:{borderWidth:1,borderColor:'#d0dacd',padding:8},selected:{backgroundColor:'#e0ebdc',borderColor:'#183d2b'},button:{backgroundColor:'#183d2b',alignSelf:'flex-start',padding:12},buttonText:{color:'#fff',fontWeight:'700',fontSize:13},item:{borderWidth:1,borderColor:'#e0e7dd',padding:12,gap:4},detail:{gap:5},name:{fontWeight:'700',color:'#183d2b',fontSize:14},link:{color:'#285a3b',fontSize:13,fontWeight:'700'},error:{color:'#a42626',fontSize:13},notice:{color:'#27613d',fontSize:13,lineHeight:20}});
